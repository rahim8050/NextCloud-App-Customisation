# Weather APIs (`weather_apis`)

This app will integrate Nextcloud with our Django Weather APIs (DRF).

Status: **pre-integration** (docs + tooling only). See `apps/weather_apis/AGENTS.md` for the security and integration contract.

## Configuration keys (contract)

These keys will be stored in Nextcloud app config under app id `weather_apis`.

| Key | Type | Purpose | Validation |
| --- | ---- | ------- | ---------- |
| `base_url` | string | Django API base URL | **HTTPS-only**, no embedded credentials, SSRF-safe host/IP (see `AGENTS.md`) |
| `api_key` | string (encrypted) | Backend auth secret | Stored encrypted via `ICrypto`; never displayed back in UI |
| `timeout_seconds` | int | Outbound HTTP timeout | TODO define bounds (recommend 1–30) |
| `dev_allow_insecure_local_http` | bool | Explicit dev override | Default `false`; when `true`, allows `http` only for allowlisted hosts (see `dev_allowlist_hosts`) |
| `dev_allowlist_hosts` | string | Hosts allowed under the dev override | Comma- or newline-separated entries; host must match exactly before private IPs/`http` are permitted |

Configure these values via Settings → Administration → Weather APIs. Secrets (API key/signing secret) are stored encrypted and are never rendered back to the browser; leave those fields blank when saving to keep existing values.

> TODO: When integration starts, document defaults and any additional non-secret toggles. Production always enforces HTTPS and public IPs; use `dev_allow_insecure_local_http` + `dev_allowlist_hosts` only for tight development exception cases.

## Integration: Backend connection

This app connects to the DRF backend through the admin-configured base URL, typically pointing at a reverse proxy in front of the Django service. Exact DRF endpoints and headers are tracked in the "Backend contract" section below and in `docs/integration.md`.

### Admin settings fields

- Base URL (`baseUrl`): Scheme + host + optional path for the DRF reverse proxy (for example `https://example.local/api`). Production must use HTTPS; the dev override is limited to allowlisted hosts.
- Client ID (`clientId`): HMAC client identifier used for the integration token handshake.
- API key (`apiKey`): Backend auth secret stored encrypted at rest; never displayed back in the UI.
- Signing secret (`signingSecret`): HMAC secret used to sign token requests; stored encrypted at rest and never displayed back in the UI.
- Timeout seconds (`timeoutSeconds`): Total timeout for outbound HTTP to the backend (bounded; see config table above).
- Dev: allow insecure local HTTP (`devAllowHttp`): Enables `http` only when the host is explicitly allowlisted.
- Dev: allowlist hosts (`devAllowlistHosts`): Comma- or newline-separated hostnames allowed when the dev override is enabled (exact match required).

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
- Timeouts: Slow or unreachable backends return `backend_timeout`; verify reverse proxy reachability and `timeout_seconds`.
- Reverse proxy headers: Ensure `Host` and `X-Forwarded-Proto` are forwarded correctly and Nextcloud overwrite settings match the proxy URL.

## Backend contract (confirmed for the integration proof-of-concept)

### Token handshake
- Endpoint: `POST {base_url}/api/v1/integration/token/`
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
  2. Path: `/api/v1/integration/token/`
  3. Query string (empty line when there is no query)
  4. Timestamp
  5. Nonce
  6. SHA256 hash of the request body (empty body hash is `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855`)

### Whoami
- Endpoint: `GET {base_url}/api/v1/integration/whoami/`
- Headers:
  * `Authorization: Bearer <access token>`
  * `X-Request-Id: <correlation id>` (propagated from the caller or a generated UUID)

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
