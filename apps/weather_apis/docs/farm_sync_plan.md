# Farm Sync (Nextcloud -> DRF) Implementation Plan
Date: March 13, 2026

## Context
Nextcloud is the system of record for farms and user identity.
The Django service is a geospatial compute backend only (NDVI, raster).
Sync is a one-way replication from Nextcloud to DRF using the existing
integration token system.

## Phased Plan

### Status (Mar 13, 2026)
- Phase 0: Done.
- Phase 1: Done.
- Phase 2: Done.
- Phase 3: N/A (Nextcloud has no farm-owning app; proxy-only).
- Phase 4: Done.
- Phase 5: Done.

### Phase 0 — Contract & Docs (Mar 13, 2026)
Goal: Freeze a clear contract without shipping new runtime behavior.
- Document the DRF sync endpoint contract:
  - `POST /api/v1/farms/sync`
  - Auth: `Authorization: Bearer <integration_token>`
  - Payload: `external_farm_id`, `external_user_id`, `name`, `bbox`, `centroid`
- Document identifier strategy:
  - Nextcloud generates `external_farm_id` (UUID).
  - Nextcloud `external_user_id` is a stable Nextcloud UID.
- Document that DRF stores farms owned by `nextcloud-integration` service user.
- Document that raster requests can use `external_farm_id`.
- Note: outbound HTTP remains behind `WeatherApiClient` per `AGENTS.md`.

### Phase 1 — Service Layer (Mar 13, 2026)
Goal: Add a dedicated service for farm sync (no new public controllers).
- Add `FarmSyncService` that:
  - Accepts a validated Farm DTO.
  - Uses `WeatherApiClient` to call `POST /api/v1/farms/sync`.
  - Uses existing integration token auth flow.
  - Logs structured metadata only (no secrets).
- Add unit tests with mocked `IClientService`.
- Add error mapping in `WeatherApiClient` for sync failures.

### Phase 2 — Admin-Only Wiring (Mar 13, 2026)
Goal: Allow admin-only manual sync (optional, behind admin UI).
- Add an admin-only settings UI action that triggers sync for a farm id.
- No public routes beyond admin settings.
- Ensure CSRF/requesttoken + password confirmation on any admin write.

### Phase 3 — Farm Event Integration (Mar 13, 2026)
Goal: Automatic sync on create/update in Nextcloud.
- N/A: Nextcloud has no farm-owning app; proxy-only architecture.

### Phase 4 — Raster Access (Mar 13, 2026)
Goal: Ensure raster endpoints can resolve external farm IDs.
- Update Nextcloud DRF proxy calls to pass `external_farm_id` where available.
- Do not change Nextcloud user auth model (still session-based).

### Phase 5 — Observability & Rollout (Mar 13, 2026)
Goal: Safe rollout and operational clarity.
- Add structured logs with correlation IDs.
- Confirm error normalization and secret redaction.
- Add docs to `docs/integration.md` for farm sync behavior.

## Non-Goals
- No HMAC auth for farm sync.
- No public endpoints for farm sync in Nextcloud.
- No direct outbound HTTP outside `WeatherApiClient`.

## Notes
- All outbound HTTP must follow SSRF rules from `AGENTS.md`.
- Secrets are stored encrypted via `ICrypto`.
