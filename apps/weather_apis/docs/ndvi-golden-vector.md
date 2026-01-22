# NDVI golden vector (DRF vs Nextcloud proxy)

## Canonical DRF endpoints (source of truth)

From `ndvi/urls.py` and the DRF OpenAPI schema:

- `GET /api/v1/farms/<farm_id>/ndvi/latest/`
- `GET /api/v1/farms/<farm_id>/ndvi/timeseries/`
- `GET /api/v1/farms/<farm_id>/ndvi/raster.png`
- `POST /api/v1/farms/<farm_id>/ndvi/raster/queue`
- `POST /api/v1/farms/<farm_id>/ndvi/refresh/`

`farm_id` is a **path** parameter. Query parameters are only the NDVI filters (for example `lookback_days`, `max_cloud`, `start`, `end`, `date`, etc.).

Required parameters from the DRF OpenAPI schema:
- Timeseries: `start` + `end` (query)
- Raster PNG: `date` (query)
- Raster queue: `date` (JSON body)

## Direct DRF call (latest)

```bash
export FARM_ID=19
export DRF_BASE=http://127.0.0.1:8001

# Use whichever auth is configured for DRF.
# Option A: JWT
curl -sS "${DRF_BASE}/api/v1/farms/${FARM_ID}/ndvi/latest/?lookback_days=14&max_cloud=30" \
  -H "Authorization: Bearer $ACCESS_TOKEN"

# Option B: API key
curl -sS "${DRF_BASE}/api/v1/farms/${FARM_ID}/ndvi/latest/?lookback_days=14&max_cloud=30" \
  -H "X-API-Key: $API_KEY"
```

Expected:
- HTTP 200 (or the expected NDVI error envelope).
- `Content-Type: application/json`.

## Nextcloud proxy call (latest)

```bash
export FARM_ID=19
export NC_BASE=https://localhost/nextcloud

# Use an authenticated admin session + request token.
curl -sS "${NC_BASE}/index.php/apps/weather_apis/api/v1/admin/farms/${FARM_ID}/ndvi/latest?lookback_days=14&max_cloud=30" \
  -H "OCS-APIRequest: true" \
  -H "requesttoken: $NC_REQUESTTOKEN" \
  -b "$NC_COOKIE"
```

Expected:
- HTTP 200.
- Nextcloud envelope: `{ "status": "ok", "data": { ... } }` where `data` contains the DRF payload.

## Direct DRF call (timeseries)

```bash
export FARM_ID=19
export DRF_BASE=http://127.0.0.1:8001

curl -sS "${DRF_BASE}/api/v1/farms/${FARM_ID}/ndvi/timeseries/?start=2024-01-01&end=2024-02-01&max_cloud=30" \
  -H "Authorization: Bearer $ACCESS_TOKEN"
```

Expected:
- HTTP 200 with `observations` or DRF error envelope.

## Nextcloud proxy call (timeseries)

```bash
export FARM_ID=19
export NC_BASE=https://localhost/nextcloud

curl -sS "${NC_BASE}/index.php/apps/weather_apis/api/v1/admin/farms/${FARM_ID}/ndvi/timeseries?start=2024-01-01&end=2024-02-01&max_cloud=30" \
  -H "OCS-APIRequest: true" \
  -H "requesttoken: $NC_REQUESTTOKEN" \
  -b "$NC_COOKIE"
```

Expected:
- HTTP 200.
- Nextcloud envelope: `{ "status": "ok", "data": { ... } }`.

## Raster queue + raster PNG

### Queue raster (DRF)

```bash
export FARM_ID=19
export DRF_BASE=http://127.0.0.1:8001

curl -sS "${DRF_BASE}/api/v1/farms/${FARM_ID}/ndvi/raster/queue" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"date":"2024-02-01"}'
```

### Queue raster (Nextcloud proxy)

```bash
export FARM_ID=19
export NC_BASE=https://localhost/nextcloud

curl -sS "${NC_BASE}/index.php/apps/weather_apis/api/v1/admin/farms/${FARM_ID}/ndvi/raster/queue" \
  -H "OCS-APIRequest: true" \
  -H "requesttoken: $NC_REQUESTTOKEN" \
  -H "Content-Type: application/json" \
  -b "$NC_COOKIE" \
  -d '{"date":"2024-02-01"}'
```

### Raster PNG (DRF)

```bash
export FARM_ID=19
export DRF_BASE=http://127.0.0.1:8001

curl -sS "${DRF_BASE}/api/v1/farms/${FARM_ID}/ndvi/raster.png?date=2024-02-01" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -o /tmp/ndvi.png
```

### Raster PNG (Nextcloud proxy)

```bash
export FARM_ID=19
export NC_BASE=https://localhost/nextcloud

curl -sS "${NC_BASE}/index.php/apps/weather_apis/api/v1/admin/farms/${FARM_ID}/ndvi/raster.png?date=2024-02-01" \
  -H "OCS-APIRequest: true" \
  -H "requesttoken: $NC_REQUESTTOKEN" \
  -b "$NC_COOKIE" \
  -o /tmp/ndvi.png
```

## UI validation (no backend call)

In **Settings → Weather APIs → Farms → NDVI**:
- Leave **Start** and **End** empty.
- Click **Timeseries**.
- Inline message shows: `Start and end dates are required.`
- No request is sent to `/ndvi/timeseries/` (verify in Nextcloud log).

If **Start** is after **End**:
- Inline message shows: `Start date must be on or before end date.`
- No request is sent to `/ndvi/timeseries/`.

## Verify the proxy is correct

- DRF access logs show: `GET /api/v1/farms/<farm_id>/ndvi/latest/ ...`
- Nextcloud log includes a debug entry `Weather API admin proxy request` with:
  - `pathTemplate` containing `/api/v1/farms/{farm_id}/ndvi/latest/`
  - `path` containing `/api/v1/farms/<farm_id>/ndvi/latest/`
  - `queryKeys` **excluding** `farmId`

If you see `farmId` in the query keys or a Nextcloud error `Unknown query parameters: farmId`, the proxy is still treating the path param as a query param.
