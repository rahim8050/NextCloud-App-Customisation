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

> TODO: When integration starts, document defaults, admin settings UI fields, and any additional non-secret toggles.

## Backend contract (placeholders)

Do not guess backend details. Replace the TODOs once the Django DRF API is finalized.

- Health: `GET {base_url}/<TODO:health-path>`
- Forecast: `GET {base_url}/<TODO:forecast-path>?lat={lat}&lon={lon}&date={date?}`
- Auth: `<TODO: header name + format>` (example: `Authorization: Bearer …` or `X-Api-Key: …`)

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
