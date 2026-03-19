# Integration: Nextcloud -> DRF

This document describes how the Nextcloud Weather APIs app connects to the Django DRF backend through a reverse proxy. For exact DRF endpoint paths and headers, see the "Backend contract" section in `README.md` and `resources/openapi.yml`.

## Flow (step list)

1. An admin configures the settings fields in Nextcloud (`baseUrl`, `INTEGRATION_HMAC_CLIENT_ID`, `INTEGRATION_HMAC_CLIENTS_JSON`, `apiKey`, `timeoutSeconds`, and dev allowlist fields).
2. The app validates the base URL (SSRF rules). `apiKey` and the base64 HMAC secret are stored encrypted at rest; the settings form never renders stored secrets (new HMAC secrets are returned once on generate/rotate).
3. Admins can generate or rotate the HMAC credentials from the settings UI without using shell commands.
4. On an integration call (for example `whoami`), `WeatherApiClient` constructs the token request (see the backend contract for the exact path and headers).
5. The request is sent to the configured reverse proxy URL, which forwards to DRF.
6. DRF returns an access token (raw or envelope-wrapped); the app unwraps `data` when present, caches the token briefly, and uses it for backend calls.
7. The app calls the integration `whoami` endpoint with the bearer token. Admins can also run a test-connection flow from the settings UI, which performs the token request and reports `expires_in`.
8. Admin diagnostics can run the status + PNG preview endpoints using the minted token, returning per-step pass/fail without exposing the token to the browser.
9. Responses are normalized into `{ "status": "ok" | "error", ... }` for most admin endpoints. The test-connection endpoint returns `{ status: 0|1, ok: bool, message, data?, code? }` for UI compatibility.
10. The admin Farms dashboard pulls a cached DRF OpenAPI schema to drive fields and operations (schema fetches are cached for ~1 hour).

## Token response shapes

WeatherApiClient accepts both token response formats:

- Raw JSON: `{ "access": "...", "expires_in": 300 }`
- Envelope: `{ "status": 0, "message": "...", "data": { "access": "...", "expires_in": 300 } }`

If an envelope is returned with `status: 1`, the client maps `errors.code` and `errors.reason` (when present) into the Nextcloud error response.

## Error mapping

| Backend/transport condition | Nextcloud error code | HTTP status |
| --- | --- | --- |
| DRF returns 401 | `unauthorized` | 401 |
| DRF returns 403 | `forbidden` | 403 |
| Timeout/network timeout | `backend_timeout` | 504 |
| DRF returns 5xx | `backend_unavailable` | 503 |
| Non-JSON payload or other non-2xx | `backend_error` | 400 |
| Invalid signature (backend) | `unauthorized` or `forbidden` | 401 or 403 |
| Token envelope `status: 1` | `errors.code` (fallback `backend_error`) | 400 |

## Security notes

- Secrets are stored encrypted at rest via `ICrypto`; the settings form never renders stored secrets (new base64 HMAC secrets are returned once on generate/rotate).
- Logs never include secrets or authorization headers; only request metadata is logged.

## Admin endpoints (Nextcloud-side)

- `POST /apps/weather_apis/settings/admin`
  - Admin-only + CSRF + password confirmation required.
  - Persists settings; response: `{ status: "ok", ok: true, message }` or the normalized error shape.
- `POST /apps/weather_apis/api/v1/admin/generate-credentials`
  - Admin-only + CSRF required.
  - Generates a client id if missing and rotates the base64 HMAC secret.
  - Response: `{ status: "ok", ok: true, message, clientId, hmacSecret }` (secrets shown once in the UI).
- `POST /apps/weather_apis/api/v1/admin/rotate-hmac`
  - Admin-only + CSRF required.
  - Rotates the base64 HMAC secret.
  - Response: `{ status: "ok", ok: true, message, clientId, hmacSecret }` (secret shown once in the UI).
- `GET /apps/weather_apis/api/v1/admin/config`
  - Admin-only.
  - Returns non-secret config only: baseUrl, clientId, timeoutSeconds, devAllowHttp, allowlistHosts, hasApiKey, hasHmacSecret, and integration status metadata.
- `POST /apps/weather_apis/api/v1/admin/test-connection`
  - Admin-only + CSRF required.
  - Performs the backend token request (HMAC + API key) and never returns the token.
  - Response: `{ status: 0, ok: true, message, data: { expires_in } }` on success, or `{ status: 1, ok: false, message, code }` on error.
- `GET /apps/weather_apis/admin/diagnostics`
  - Admin-only.
  - Mints a token, calls `/integrations/nextcloud/status/`, calls `/integrations/nextcloud/preview.png`.
  - Response: `{ status: "ok", ok: true, message, data: { token, status, png } }` (no tokens returned).
- `GET /apps/weather_apis/admin/preview.png`
  - Admin-only.
  - Streams the DRF PNG preview via Nextcloud; browser never sees the token.
- `GET /apps/weather_apis/api/v1/admin/farms/schema`
  - Admin-only.
  - Returns `{ status: "ok", data: { schema, warning? } }` where `schema` includes farm fields + operations derived from the DRF OpenAPI schema.
  - Schema source: `GET {baseUrl}/api/schema/?format=openapi-json` (cached ~1 hour).
- `POST /apps/weather_apis/api/v1/admin/farms/list`
  - Admin-only + CSRF required.
  - Proxies DRF list endpoint; accepts pagination query params defined in schema.
- `POST /apps/weather_apis/api/v1/admin/farms/create`
  - Admin-only + CSRF required.
  - Proxies DRF create endpoint; required fields = `required - readOnly`.
- `GET /apps/weather_apis/api/v1/admin/farms/{id}`
  - Admin-only.
  - Proxies DRF retrieve endpoint.
- `PUT /apps/weather_apis/api/v1/admin/farms/{id}`
  - Admin-only + CSRF required.
  - Proxies DRF update endpoint; required fields = `required - readOnly`.
- `PATCH /apps/weather_apis/api/v1/admin/farms/{id}`
  - Admin-only + CSRF required.
  - Proxies DRF partial update endpoint; strips read-only fields.
- `DELETE /apps/weather_apis/api/v1/admin/farms/{id}`
  - Admin-only + CSRF required.
  - Proxies DRF delete endpoint.
- `GET /apps/weather_apis/api/v1/admin/farms/{farm_id}/ndvi/latest`
  - Admin-only.
  - Proxies DRF NDVI latest endpoint; query params are schema-driven.
- `GET /apps/weather_apis/api/v1/admin/farms/{farm_id}/ndvi/timeseries`
  - Admin-only.
  - Proxies DRF NDVI timeseries endpoint; query params are schema-driven.
- `GET /apps/weather_apis/api/v1/admin/farms/{farm_id}/ndvi/raster.png`
  - Admin-only.
  - Streams raw `image/png` bytes via Nextcloud (no JSON wrapper, no token exposure).
  - Response headers: `Content-Type: image/png`, `Cache-Control: no-store`.
  - Optional query param: `external_farm_id` (UUID) for synced farms.
- `POST /apps/weather_apis/api/v1/admin/farms/{farm_id}/ndvi/raster/queue`
  - Admin-only + CSRF required.
  - Proxies DRF NDVI raster queue endpoint; request body is schema-driven.
  - Optional query param: `external_farm_id` (UUID) for synced farms.
- `POST /apps/weather_apis/api/v1/admin/farms/{farm_id}/ndvi/refresh`
  - Admin-only + CSRF required.
  - Proxies DRF NDVI refresh endpoint; request body is schema-driven.
- `GET /apps/weather_apis/api/v1/admin/farms/{farm_id}/weather/current`
  - Admin-only.
  - Proxies DRF farm weather current endpoint; returns DRF payload as-is.
- `GET /apps/weather_apis/api/v1/admin/farms/{farm_id}/weather/hourly`
  - Admin-only.
  - Proxies DRF farm weather hourly endpoint; query param `hours` (default 48).
- `GET /apps/weather_apis/api/v1/admin/farms/{farm_id}/weather/daily`
  - Admin-only.
  - Proxies DRF farm weather daily endpoint; query param `days` (default 7).
- `GET /apps/weather_apis/api/v1/admin/farms/{farm_id}/observations`
  - Admin-only.
  - Proxies DRF farm observation list endpoint.
  - Optional query params: `start`, `end`, `event_type`, `limit`, `offset`.
- `POST /apps/weather_apis/api/v1/admin/farms/{farm_id}/observations`
  - Admin-only + CSRF required.
  - Proxies DRF farm observation create endpoint; request body is schema-driven.
- `GET /apps/weather_apis/api/v1/admin/farms/{farm_id}/observations/{observation_id}`
  - Admin-only.
  - Proxies DRF farm observation retrieve endpoint.
- `PATCH /apps/weather_apis/api/v1/admin/farms/{farm_id}/observations/{observation_id}`
  - Admin-only + CSRF required.
  - Proxies DRF farm observation update endpoint; request body is schema-driven.
- `DELETE /apps/weather_apis/api/v1/admin/farms/{farm_id}/observations/{observation_id}`
  - Admin-only + CSRF required.
  - Proxies DRF farm observation delete endpoint.

### Observation Metadata Schema (Admin UI)

The admin UI enforces a structured metadata schema for farm observations (no raw JSON entry). The UI builds a `metadata` object and sends it in the request body when present.

Core metadata (optional unless noted):
- `source` (enum): `manual`, `sensor`, `integration`
- `observer` (string)
- `crop` (string)
- `variety` (string)
- `growth_stage` (enum): `preplant`, `emergence`, `vegetative`, `flowering`, `fruiting`, `maturity`, `postharvest`
- `area_ha` (number)
- `location_note` (string)

Event-specific metadata (only shown in the UI for matching `event_type`):
- `planting`:
  - `seed_rate_kg_ha` (number)
  - `planting_method` (enum): `broadcast`, `row`, `transplant`
- `irrigation`:
  - `irrigation_type` (enum): `drip`, `sprinkler`, `flood`, `other`
  - `water_mm` (number)
- `fertilization`:
  - `fertilizer_type` (string)
  - `nutrient_n_kg_ha` (number)
  - `nutrient_p_kg_ha` (number)
  - `nutrient_k_kg_ha` (number)
- `pest_control`:
  - `pest` (string)
  - `product` (string)
  - `dose_ml_ha` (number)
- `harvest`:
  - `yield_kg` (number)
  - `moisture_percent` (number)
- `scouting` / `soil_test`:
  - `pest_pressure` (enum): `none`, `low`, `medium`, `high`
  - `soil_ph` (number)
  - `organic_matter_percent` (number)

`apiKey` (wk_live_...) is still provisioned by DRF; Nextcloud only generates and rotates `clientId` + base64 `hmacSecret`.
- SSRF defenses include strict base URL validation (HTTPS-only in production, no embedded credentials, no localhost), DNS resolution checks, dev allowlists, redirects disabled, and bounded timeouts.
- All outbound HTTP uses Nextcloud `IClientService`.
- `X-Request-Id` is propagated to the backend for tracing.

## Farm Sync Plan

For the phased Nextcloud -> DRF farm sync implementation plan, see `docs/farm_sync_plan.md`.

### Farm Sync Idempotency

- DRF's `POST /api/v1/farms/sync` honors an `Idempotency-Key` header (trimmed to 191 bytes). Once a successful sync has been recorded for that key, subsequent requests with the same key immediately replay the original envelope instead of persisting another farm.
- Nextcloud generates the header from the farm's `external_farm_id` (`farm-sync:<external_farm_id>`) and forwards it via `FarmSyncService`, so the admin UI can safely retry without creating duplicate records. The Django backend still logs each attempt for observability (`farm_sync_created` / `farm_sync_updated`).

## Farm Sync Observability (Admin)

Nextcloud will surface farm sync diagnostics in the admin UI, using the same
structured logging conventions as DRF. DRF emits `farm_sync_created`,
`farm_sync_updated`, and `farm_sync_failed` with `external_farm_id`,
`external_user_id`, and `client_id` (see `~/projects/weather-apis`).
Admin views should link to these events for troubleshooting without exposing
secrets.
