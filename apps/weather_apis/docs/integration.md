# Integration: Nextcloud -> DRF

This document describes how the Nextcloud Weather APIs app connects to the Django DRF backend through a reverse proxy. For exact DRF endpoint paths and headers, see the "Backend contract" section in `README.md` and `resources/openapi.yml`.

## Flow (step list)

1. An admin configures the settings fields in Nextcloud (base URL, client ID, API key, signing secret, timeouts).
2. The app validates the base URL (SSRF rules) and stores secrets encrypted at rest.
3. On an integration call (for example `whoami`), `WeatherApiClient` constructs the token request (see the backend contract for the exact path and headers).
4. The request is sent to the configured reverse proxy URL, which forwards to DRF.
5. DRF returns an access token; the app caches it briefly and uses it for backend calls.
6. The app calls the integration `whoami` endpoint with the bearer token. A health/ping check may be added later (TODO path).
7. Responses are normalized into `{ "status": "ok" | "error", ... }` and include a `requestId` for correlation.

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

- Secrets (`api_key`, `signing_secret`) are encrypted at rest via `ICrypto` and are never rendered back in the UI.
- Logs never include secrets or authorization headers; only request metadata is logged.
- SSRF defenses include strict base URL validation (HTTPS-only in production, no embedded credentials, no localhost), DNS resolution checks, dev allowlists, redirects disabled, and bounded timeouts.
- All outbound HTTP uses Nextcloud `IClientService`.
- `X-Request-Id` is propagated to the backend for tracing.
