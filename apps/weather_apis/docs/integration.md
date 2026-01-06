# Integration: Nextcloud -> DRF

This document describes how the Nextcloud Weather APIs app connects to the Django DRF backend through a reverse proxy. For exact DRF endpoint paths and headers, see the "Backend contract" section in `README.md` and `resources/openapi.yml`.

## Flow (step list)

1. An admin configures the settings fields in Nextcloud (`baseUrl`, `clientId`, `apiKey`, `hmacSecret`, `timeoutSeconds`, and dev allowlist fields).
2. The app validates the base URL (SSRF rules). `apiKey` and `hmacSecret` are stored encrypted at rest; the settings form never renders stored secrets (new HMAC secrets are returned once on generate/rotate).
3. Admins can generate or rotate the HMAC credentials from the settings UI without using shell commands.
4. On an integration call (for example `whoami`), `WeatherApiClient` constructs the token request (see the backend contract for the exact path and headers).
5. The request is sent to the configured reverse proxy URL, which forwards to DRF.
6. DRF returns an access token; the app caches it briefly and uses it for backend calls.
7. The app calls the integration `whoami` endpoint with the bearer token. Admins can also run a signed ping check against `/api/v1/integrations/nextcloud/ping/` from the settings UI.
8. Responses are normalized into `{ "status": "ok" | "error", ... }` and include a `requestId` for correlation.

## Error mapping

| Backend/transport condition | Nextcloud error code | HTTP status |
| --- | --- | --- |
| DRF returns 401 | `unauthorized` | 401 |
| DRF returns 403 | `forbidden` | 403 |
| Timeout/network timeout | `backend_timeout` | 504 |
| DRF returns 5xx | `backend_unavailable` | 503 |
| Non-JSON payload or other non-2xx | `backend_error` | 400 |
| Invalid signature (backend) | `unauthorized` or `forbidden` | 401 or 403 |

## Security notes

- Secrets are stored encrypted at rest via `ICrypto`; the settings form never renders stored secrets (new HMAC secrets are returned once on generate/rotate).
- When `hmacSecret` is rotated, the previous secret is kept in `hmacSecretPrevious` with an expiry timestamp (`hmacSecretPreviousExpiresAt`) for a 24h grace window.
- Logs never include secrets or authorization headers; only request metadata is logged.

## Admin endpoints (Nextcloud-side)

- `POST /apps/weather_apis/api/v1/admin/generate-credentials`
  - Admin-only + CSRF required.
  - Generates a client id if missing and rotates the HMAC secret.
  - Response: `{ ok: true, clientId, hmacSecret }` (secrets shown once in the UI).
- `POST /apps/weather_apis/api/v1/admin/rotate-hmac`
  - Admin-only + CSRF required.
  - Rotates the HMAC secret.
  - Response: `{ ok: true, hmacSecret }` (secret shown once in the UI).
- `GET /apps/weather_apis/api/v1/admin/config`
  - Admin-only.
  - Returns non-secret config only: baseUrl, clientId, timeoutSeconds, devAllowHttp, allowlistHosts, hasApiKey, hasHmacSecret, and rotation metadata.
- `POST /apps/weather_apis/api/v1/admin/test-connection`
  - Admin-only + CSRF required.
  - Calls the backend ping endpoint with HMAC headers.
  - Response: `{ status: "ok" | "error", message, data }` (no secrets).

`apiKey` (wk_live_...) is still provisioned by DRF; Nextcloud only generates and rotates `clientId` + `hmacSecret`.
- SSRF defenses include strict base URL validation (HTTPS-only in production, no embedded credentials, no localhost), DNS resolution checks, dev allowlists, redirects disabled, and bounded timeouts.
- All outbound HTTP uses Nextcloud `IClientService`.
- `X-Request-Id` is propagated to the backend for tracing.
