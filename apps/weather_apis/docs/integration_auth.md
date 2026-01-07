# Integration Auth (Nextcloud ↔ DRF)

This document defines the shared HMAC contract and the Nextcloud-specific configuration rules.

## Unified HMAC contract

Required values (same names in both repos):

- `INTEGRATION_HMAC_CLIENT_ID`
  - HMAC client id (case-sensitive).
- `INTEGRATION_HMAC_CLIENTS_JSON`
  - JSON object mapping `client_id -> secret_b64`.
  - Example: `{"<client_id>":"<base64-secret>"}`.
  - Secrets must decode with strict base64; decoded bytes are used for HMAC.
  - Whitespace is trimmed; `client_id` is case-sensitive and must exist as a key in the JSON.

Legacy behavior:

- Legacy appconfig keys are blocked by default.
- Set `INTEGRATION_LEGACY_CONFIG_ALLOWED=1` in Nextcloud system config only for migration (no fallback is performed).

## Where to set values

- Nextcloud app config (Settings → Administration → Weather APIs):
  - Stores `INTEGRATION_HMAC_CLIENT_ID` and `INTEGRATION_HMAC_CLIENTS_JSON`.
  - Secrets are encrypted at rest via `ICrypto`.
- Optional system config override (config.php):
  - Use the same key names to override app config values.

## Signing rules (canonicalization)

For every signed request:

1. Method: uppercase HTTP method (e.g., `GET`).
2. Path: exact request path, leading slash required. Trailing slash is significant.
3. Query string:
   - Parse on `&`, keep blank values, preserve duplicates.
   - Decode percent-encoding and convert `+` to space.
   - Re-encode with RFC3986-safe characters (`-_.~`), spaces as `%20`.
   - Sort by `(encoded_key, encoded_value)` and re-join with `&`.
4. Timestamp: unix seconds.
5. Nonce: random unique string.
6. Body hash: SHA-256 hex of raw request bytes.
   - For `GET`, hash the empty body (`""`).

Canonical string (newline-delimited, no trailing newline):
```
METHOD
PATH
CANONICAL_QUERY
TIMESTAMP
NONCE
BODY_SHA256
```

Signature: HMAC-SHA256 over the canonical string, hex-encoded.

## Header names

- Client id: `X-Client-Id` (preferred), `X-NC-CLIENT-ID` (alias)
- Timestamp: `X-Timestamp` (preferred), `X-NC-TIMESTAMP` (alias)
- Nonce: `X-Nonce` (preferred), `X-NC-NONCE` (alias)
- Signature: `X-Signature` (preferred), `X-NC-SIGNATURE` (alias)
- API key (token bootstrap only): `X-API-Key`
- Request id (correlation): `X-Request-Id`

## Redirects

Signed requests must not follow redirects. A 301/302 indicates a path mismatch and must be fixed at the proxy or base URL.

## Rotation strategy

1. Generate a new base64 secret in Nextcloud (Generate/Rotate).
2. Update DRF `INTEGRATION_HMAC_CLIENTS_JSON` with the new base64 secret.
3. Verify with “Check configuration” in Nextcloud and a signed ping against DRF.

## Troubleshooting codes

| Code | Meaning | Fix |
| --- | --- | --- |
| `missing_config` | Required keys missing | Set `INTEGRATION_HMAC_CLIENT_ID` and `INTEGRATION_HMAC_CLIENTS_JSON`. |
| `blocked_legacy_present` | Legacy keys detected and blocked | Remove legacy keys or set `INTEGRATION_LEGACY_CONFIG_ALLOWED=1` temporarily. |
| `bad_json` | Invalid JSON or empty mapping | Provide a JSON object map of `client_id -> secret_b64`. |
| `bad_base64` | Secret fails strict base64 decode | Re-encode secret with strict base64. |
| `unknown_client` | Client id not found in JSON mapping | Ensure `INTEGRATION_HMAC_CLIENT_ID` exists in JSON. |
| `missing_headers` | Required signature headers missing | Confirm header names and values. |
| `sig_mismatch` | Signature mismatch | Recompute canonical string and secret. |
| `method_mismatch` | Method differs from signed method | Verify method and canonical string. |
| `path_mismatch` | Path differs from signed path | Ensure trailing slash + proxy path are exact. |
| `body_hash_mismatch` | Body hash mismatch | Use raw bytes; GET hashes empty body. |
| `skew` | Timestamp outside skew window | Sync clocks. |
| `replay` | Nonce replayed | Use unique nonce per request. |

## Legacy cleanup (Nextcloud)

Remove legacy keys after migrating:

```bash
occ config:app:delete weather_apis clientId
occ config:app:delete weather_apis hmacSecret
occ config:app:delete weather_apis hmacSecretPrevious
occ config:app:delete weather_apis hmacSecretPreviousExpiresAt
occ config:app:delete weather_apis hmac_client_id
occ config:app:delete weather_apis hmac_secret
occ config:app:delete weather_apis signingSecret
occ config:app:delete weather_apis base_url
occ config:app:delete weather_apis api_key
occ config:app:delete weather_apis timeout_seconds
occ config:app:delete weather_apis dev_allow_insecure_local_http
occ config:app:delete weather_apis dev_allowlist_hosts
occ config:app:delete weather_apis devAllowlistHosts
```

`baseUrl`, `apiKey`, and timeouts must still be configured via the admin UI.

## Definition of done

- With valid Nextcloud config + DRF `INTEGRATION_HMAC_CLIENTS_JSON` set, `GET /api/v1/integrations/nextcloud/ping/` returns 200.
- Misconfigured Nextcloud shows a status-only warning and performs no outbound HTTP.
- No secrets appear in logs.
