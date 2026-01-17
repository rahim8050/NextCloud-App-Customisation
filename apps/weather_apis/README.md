# Weather APIs (`weather_apis`)

This app will integrate Nextcloud with our Django Weather APIs (DRF).

Status: **pre-integration** (docs + tooling only). See `apps/weather_apis/AGENTS.md` for the security and integration contract.

## Configuration keys (contract)

These keys are stored in Nextcloud app config under app id `weather_apis`. System config values (config.php) with the same names take precedence.

| Key | Type | Purpose | Validation |
| --- | ---- | ------- | ---------- |
| `baseUrl` | string | Django API base URL | **HTTPS-only**, no embedded credentials, SSRF-safe host/IP (see `AGENTS.md`) |
| `INTEGRATION_HMAC_CLIENT_ID` | string | DRF integration client id | Required; must exist as a key in `INTEGRATION_HMAC_CLIENTS_JSON` |
| `INTEGRATION_HMAC_CLIENTS_JSON` | string (encrypted) | JSON map of `client_id -> secret_b64` | Required; strict base64 decode; non-empty mapping |
| `apiKey` | string (encrypted) | Backend auth secret | Stored encrypted via `ICrypto`; never displayed back in UI |
| `timeoutSeconds` | int | Outbound HTTP timeout | TODO define bounds (recommend 1–30) |
| `devAllowHttp` | bool | Explicit dev override | Default `false`; when `true`, allows `http` only for allowlisted hosts |
| `allowlistHosts` | string | Hosts allowed under the dev override | Comma- or newline-separated entries; host must match exactly before private IPs/`http` are permitted |

Legacy keys (`clientId`, `hmacSecret`, `hmacSecretPrevious`, `hmacSecretPreviousExpiresAt`, `hmac_client_id`, `hmac_secret`, `signingSecret`, `base_url`, `api_key`, `timeout_seconds`, `dev_allow_insecure_local_http`, `dev_allowlist_hosts`) are **blocked by default**. Remove them or temporarily set `INTEGRATION_LEGACY_CONFIG_ALLOWED=1` in system config while migrating (no fallback is performed).

Configure these values via Settings → Administration → Weather APIs. Secrets are write-only in the form; generate/rotate returns a new base64 HMAC secret once. Leave those fields blank when saving to keep existing values.

> TODO: When integration starts, document defaults and any additional non-secret toggles. Production always enforces HTTPS and public IPs; use `devAllowHttp` + `allowlistHosts` only for tight development exception cases.

## Integration setup

1. Configure Nextcloud settings (base URL, client id, base64 HMAC secret, API key, timeouts).
2. Copy the export snippet into DRF:
   - `INTEGRATION_HMAC_CLIENT_ID`
   - `INTEGRATION_HMAC_CLIENTS_JSON`
3. Use “Test connection” or “Run diagnostics” in the admin UI to verify token minting, status, and PNG preview.

See `docs/integration_auth.md` for the signing contract and troubleshooting guide,
and `docs/hmac_audit.md` for the cross-repo HMAC audit notes.

## Integration: Backend connection

This app connects to the DRF backend through the admin-configured base URL, typically pointing at a reverse proxy in front of the Django service. Exact DRF endpoints and headers are tracked in the "Backend contract" section below and in `docs/integration.md`.

### Admin settings fields

- Base URL (`baseUrl`): Scheme + host + optional path for the DRF reverse proxy (for example `https://example.local/api`). Production must use HTTPS; the dev override is limited to allowlisted hosts.
- Client ID (`INTEGRATION_HMAC_CLIENT_ID`): HMAC client identifier used for the integration token handshake.
- API key (`apiKey`): Backend auth secret stored encrypted at rest; never displayed back in the UI.
- HMAC secret (`INTEGRATION_HMAC_CLIENTS_JSON`): Base64-encoded secret used for HMAC signing; stored encrypted at rest via `ICrypto`. Newly generated/rotated secrets are returned once; stored values are not displayed.
- Timeout seconds (`timeoutSeconds`): Total timeout for outbound HTTP to the backend (bounded; see config table above).
- Dev: allow insecure local HTTP (`devAllowHttp`): Enables `http` only when the host is explicitly allowlisted.
- Dev: allowlist hosts (`allowlistHosts`): Comma- or newline-separated hostnames allowed when the dev override is enabled (exact match required).

### Admin credential actions

In Settings → Administration → Weather APIs, admins can generate and rotate HMAC credentials without using shell commands.

- Generate client + secret: `POST /apps/weather_apis/api/v1/admin/generate-credentials` (admin-only, CSRF required)
  - Generates a client id if missing and rotates the base64 HMAC secret.
  - Returns `{ status: "ok", ok: true, message, clientId, hmacSecret }` once; the UI shows the secret only after the request and includes an export snippet for DRF.
- Rotate secret: `POST /apps/weather_apis/api/v1/admin/rotate-hmac` (admin-only, CSRF required)
  - Always rotates the base64 HMAC secret.
  - Returns `{ status: "ok", ok: true, message, clientId, hmacSecret }` once; the UI shows the secret only after the request.
- Test connection: `POST /apps/weather_apis/api/v1/admin/test-connection` (admin-only, CSRF required)
  - Performs a backend token request (HMAC + API key) and never returns the token.
  - Returns `{ status: 0, ok: true, message, data: { expires_in } }` on success, or `{ status: 1, ok: false, message, code }` on error.
- Diagnostics: `GET /apps/weather_apis/admin/diagnostics` (admin-only)
  - Mints a token, calls `/integrations/nextcloud/status/`, calls `/integrations/nextcloud/preview.png`.
  - Returns `{ status: "ok", ok: true, message, data: { token, status, png } }` (no tokens returned).
- Preview proxy: `GET /apps/weather_apis/admin/preview.png` (admin-only)
  - Streams the DRF PNG preview through Nextcloud; tokens never reach the browser.

`apiKey` (wk_live_...) still comes from DRF; Nextcloud only generates `clientId` + base64 `hmacSecret`.

### Connectivity test

Direct app route:
```bash
curl -u admin:APP_PASSWORD \
  -H "Accept: application/json" \
  "https://nextcloud.example.com/index.php/apps/weather_apis/api/v1/integration/whoami"
```

OCS route:
```bash
curl -u admin:APP_PASSWORD \
  -H "OCS-APIRequest: true" \
  "https://nextcloud.example.com/ocs/v2.php/apps/weather_apis/api/v1/integration/whoami?format=json"
```

### Troubleshooting

- CSRF/requesttoken: Admin settings saves require a valid `requesttoken` (use the UI or send the header when posting).
- Permissions: Settings and integration endpoints are admin-only; non-admin sessions return 401/403.
- Allowlist/SSRF blocks: Base URL validation fails if HTTPS is missing, the host is disallowed, or DNS resolves to blocked ranges.
- Timeouts: Slow or unreachable backends return `backend_timeout`; verify reverse proxy reachability and `timeoutSeconds`.
- Reverse proxy headers: Ensure `Host` and `X-Forwarded-Proto` are forwarded correctly and Nextcloud overwrite settings match the proxy URL.

## Backend contract (confirmed for the integration proof-of-concept)

### Token handshake
- Endpoint: `POST {baseUrl}/api/v1/integrations/token/`
- Headers:
  * `Content-Type: application/json`
  * `X-API-Key: <api key>`
  * `X-Client-Id: <IntegrationClient.client_id UUID>`
  * `X-Timestamp: <unix seconds>`
  * `X-Nonce: <random string>`
  * `X-Signature: <hex hmac sha256>`
- Body: empty
- Signature canonical string (lines separated by `\n`, blank line if query string is empty):
  1. HTTP method: `POST`
  2. Path: `/api/v1/integrations/token/`
  3. Query string (empty line when there is no query)
  4. Timestamp
  5. Nonce
  6. SHA256 hash of the request body (empty body hash is `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855`)

### Whoami
- Endpoint: `GET {baseUrl}/api/v1/integrations/whoami/`
- Headers:
  * `Authorization: Bearer <access token>`
  * `X-Request-Id: <correlation id>` (propagated from the caller or a generated UUID)

### Ping (HMAC-only)
- Endpoint: `GET {baseUrl}/api/v1/integrations/nextcloud/ping/`
- Headers:
  * `X-Client-Id: <IntegrationClient.client_id UUID>`
  * `X-Timestamp: <unix seconds>`
  * `X-Nonce: <random string>`
  * `X-Signature: <hex hmac sha256>`

### Diagnostics status
- Endpoint: `GET {baseUrl}/api/v1/integrations/nextcloud/status/`
- Headers:
  * `Authorization: Bearer <access token>`
- Response `data`: `{ ok, server_time, version, capabilities: { png_preview } }`

### Diagnostics preview
- Endpoint: `GET {baseUrl}/api/v1/integrations/nextcloud/preview.png`
- Headers:
  * `Authorization: Bearer <access token>`
- Response: `image/png` (binary), `Cache-Control: no-store`

## Normalized API responses (Nextcloud-side)

Success:
```json
{ "status": "ok", "data": {} }
```

Error:
```json
{
  "status": "error",
  "error": {
    "code": "invalid_argument|unauthorized|forbidden|backend_timeout|backend_unavailable|backend_error",
    "message": "Safe human-readable message",
    "requestId": "reqId-from-nextcloud-or-generated",
    "details": {}
  }
}
```

## Development

See `apps/weather_apis/CONTRIBUTING.md`.

## Dev / Integration testing

To avoid curl “malformed URL” errors and to keep repeatable requests to the proxy endpoints, use the helper script:

```bash
scripts/curl_json.sh "http://nextcloud/apps/weather_apis/api/v1/integration/whoami" --file payload.json
scripts/curl_json.sh --get "http://nextcloud/ocs/v2.php/apps/weather_apis/api/v1/integration/whoami?format=json"
```

The script strips CR/LF from the URL, rejects whitespace, and always emits either a POST with `Content-Type: application/json` or a GET request depending on the arguments.
