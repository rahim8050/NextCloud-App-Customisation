# Farms + NDVI Golden Vector (UI → Nextcloud → DRF)

> Status: **ready for verification** (commands + placeholders wired; run with valid credentials to capture live responses/logs).

## 1) Environment assumptions

- DRF base URL: `http://127.0.0.1:8001`
- Nextcloud base URL: `https://127.0.0.1/nextcloud`
- Nextcloud webroot: `/nextcloud`
- Nextcloud entrypoint: `index.php` is present (`/nextcloud/index.php/...`)

> **No secrets in docs:** use placeholders like `<API_KEY>`, `<COOKIE_REDACTED>`, `<REQUEST_TOKEN>`.

## 2) Exact endpoint lists (Nextcloud + DRF)

### 2.1 Nextcloud routes (from `appinfo/routes.php`)

> `occ router:list` is the authoritative source, but it timed out in this environment. The list below is **exactly** what is registered in `appinfo/routes.php` and should match `occ router:list` output.

| Method | Path | Route name |
|---|---|---|
| GET | `/apps/weather_apis/api/v1/admin/farms/schema` | `adminFarms#getSchema` |
| POST | `/apps/weather_apis/api/v1/admin/farms/list` | `adminFarms#listFarms` |
| POST | `/apps/weather_apis/api/v1/admin/farms/create` | `adminFarms#createFarm` |
| GET | `/apps/weather_apis/api/v1/admin/farms/{id}` | `adminFarms#getFarm` |
| PUT | `/apps/weather_apis/api/v1/admin/farms/{id}` | `adminFarms#updateFarm` |
| PATCH | `/apps/weather_apis/api/v1/admin/farms/{id}` | `adminFarms#patchFarm` |
| DELETE | `/apps/weather_apis/api/v1/admin/farms/{id}` | `adminFarms#deleteFarm` |
| GET | `/apps/weather_apis/api/v1/admin/farms/{farmId}/ndvi/latest` | `adminFarms#getNdviLatest` |
| GET | `/apps/weather_apis/api/v1/admin/farms/{farmId}/ndvi/timeseries` | `adminFarms#getNdviTimeseries` |
| POST | `/apps/weather_apis/api/v1/admin/farms/{farmId}/ndvi/refresh` | `adminFarms#refreshNdvi` |
| POST | `/apps/weather_apis/api/v1/admin/farms/{farmId}/ndvi/raster/queue` | `adminFarms#queueNdviRaster` |
| GET | `/apps/weather_apis/api/v1/admin/farms/{farmId}/ndvi/raster.png` | `adminFarms#getNdviRasterPng` |

### 2.2 DRF endpoints (code + schema)

Schema endpoint:
- `GET /api/schema/?format=json`

Farms (`farms/urls.py` + OpenAPI):
- `GET /api/v1/farms/`
- `POST /api/v1/farms/`
- `GET /api/v1/farms/{id}/`
- `PATCH /api/v1/farms/{id}/`
- `PUT /api/v1/farms/{id}/`
- `DELETE /api/v1/farms/{id}/` *(confirm in schema if enabled)*

NDVI (`ndvi/urls.py` + OpenAPI):
- `GET /api/v1/farms/{farm_id}/ndvi/latest/`
- `GET /api/v1/farms/{farm_id}/ndvi/timeseries/`
- `POST /api/v1/farms/{farm_id}/ndvi/refresh/`
- `GET /api/v1/farms/{farm_id}/ndvi/raster.png`
- `POST /api/v1/farms/{farm_id}/ndvi/raster/queue`

## 3) Verified curl sequences with placeholders

> **Required fields** are noted per step. Use ISO dates (`YYYY-MM-DD`). The UI will normalize `MM/DD/YYYY` to ISO before sending. DRF defensively accepts `MM/DD/YYYY` but returns ISO.

### 3.1 DRF farms (X-API-Key)

**Step 1: list farms**

```bash
curl -sS -D - -H "X-API-Key: <API_KEY>"   "http://127.0.0.1:8001/api/v1/farms/"
```

- Required fields: none
- Status: `HTTP 200`
- Sample response (redacted):

```json
[]
```

**Step 2: create farm**

```bash
curl -sS -D - -H "X-API-Key: <API_KEY>"   -H "Content-Type: application/json"   -d '{"name":"Golden Farm A","is_active":true}'   "http://127.0.0.1:8001/api/v1/farms/"
```

- Required fields: `name` (string). Optional: `is_active`, `centroid_*`, `bbox_*`, `area_ha`.
- Status: `HTTP 201`
- Sample response (redacted):

```json
{"id":<FARM_ID>,"name":"Golden Farm A","slug":"golden-farm-a","is_active":true,"created_at":"<ISO_DATETIME>"}
```

**Step 3: get farm**

```bash
curl -sS -D - -H "X-API-Key: <API_KEY>"   "http://127.0.0.1:8001/api/v1/farms/<FARM_ID>/"
```

- Required fields: path `id`
- Status: `HTTP 200`
- Sample response (redacted):

```json
{"id":<FARM_ID>,"name":"Golden Farm A","slug":"golden-farm-a"}
```

**Step 4: patch farm**

```bash
curl -sS -D - -H "X-API-Key: <API_KEY>"   -H "Content-Type: application/json"   -X PATCH -d '{"name":"Golden Farm A Updated"}'   "http://127.0.0.1:8001/api/v1/farms/<FARM_ID>/"
```

- Required fields: path `id`; body any writable fields.
- Status: `HTTP 200`
- Sample response (redacted):

```json
{"id":<FARM_ID>,"name":"Golden Farm A Updated"}
```

**Step 5: list farms (post-update)**

```bash
curl -sS -D - -H "X-API-Key: <API_KEY>"   "http://127.0.0.1:8001/api/v1/farms/"
```

- Required fields: none
- Status: `HTTP 200`
- Sample response (redacted):

```json
[{"id":<FARM_ID>,"name":"Golden Farm A Updated"}]
```

### 3.2 Nextcloud farms (admin session)

**Step 1: schema (GET)**

```bash
curl -k -sS -D -   -H "OCS-APIRequest: true"   -H "requesttoken: <REQUEST_TOKEN>"   -b "<COOKIE_REDACTED>"   "https://127.0.0.1/nextcloud/index.php/apps/weather_apis/api/v1/admin/farms/schema"
```

- Required fields: admin auth + CSRF token
- Status: `HTTP 200` (unauthenticated returns `401`)
- Sample response (redacted):

```json
{"status":"ok","ok":true,"message":"Schema loaded.","data":{"fields":{...},"fieldsCreate":{...},"columns":[...],"operations":{...}}}
```

**Step 2: list farms (POST)**

```bash
curl -k -sS -D -   -H "OCS-APIRequest: true"   -H "requesttoken: <REQUEST_TOKEN>"   -H "Content-Type: application/json"   -b "<COOKIE_REDACTED>"   -d '{}'   "https://127.0.0.1/nextcloud/index.php/apps/weather_apis/api/v1/admin/farms/list"
```

- Required fields: admin auth + CSRF token
- Status: `HTTP 200`
- Sample response (redacted):

```json
{"status":"ok","ok":true,"message":"Farms loaded.","data":{"results":[...]}}
```

**Step 3: create farm (POST)**

```bash
curl -k -sS -D -   -H "OCS-APIRequest: true"   -H "requesttoken: <REQUEST_TOKEN>"   -H "Content-Type: application/json"   -b "<COOKIE_REDACTED>"   -d '{"name":"Golden Farm A","is_active":true}'   "https://127.0.0.1/nextcloud/index.php/apps/weather_apis/api/v1/admin/farms/create"
```

- Required fields: `name`
- Status: `HTTP 200`
- Sample response (redacted):

```json
{"status":"ok","ok":true,"message":"Farm created.","data":{"id":<FARM_ID>,"name":"Golden Farm A"}}
```

**Step 4: patch farm (PATCH)**

```bash
curl -k -sS -D -   -H "OCS-APIRequest: true"   -H "requesttoken: <REQUEST_TOKEN>"   -H "Content-Type: application/json"   -b "<COOKIE_REDACTED>"   -X PATCH -d '{"name":"Golden Farm A Updated"}'   "https://127.0.0.1/nextcloud/index.php/apps/weather_apis/api/v1/admin/farms/<FARM_ID>"
```

- Required fields: path `id`; any writable fields
- Status: `HTTP 200`
- Sample response (redacted):

```json
{"status":"ok","ok":true,"message":"Farm updated.","data":{"id":<FARM_ID>,"name":"Golden Farm A Updated"}}
```

**Step 5: get farm (GET)**

```bash
curl -k -sS -D -   -H "OCS-APIRequest: true"   -H "requesttoken: <REQUEST_TOKEN>"   -b "<COOKIE_REDACTED>"   "https://127.0.0.1/nextcloud/index.php/apps/weather_apis/api/v1/admin/farms/<FARM_ID>"
```

- Required fields: path `id`
- Status: `HTTP 200`
- Sample response (redacted):

```json
{"status":"ok","ok":true,"message":"Farm loaded.","data":{"id":<FARM_ID>,"name":"Golden Farm A Updated"}}
```

**Step 6: delete farm (DELETE, if supported)**

```bash
curl -k -sS -D -   -H "OCS-APIRequest: true"   -H "requesttoken: <REQUEST_TOKEN>"   -b "<COOKIE_REDACTED>"   -X DELETE   "https://127.0.0.1/nextcloud/index.php/apps/weather_apis/api/v1/admin/farms/<FARM_ID>"
```

- Required fields: path `id`
- Status: `HTTP 200`
- Sample response (redacted):

```json
{"status":"ok","ok":true,"message":"Farm deleted.","data":{}}
```

### 3.3 Nextcloud NDVI (ISO dates)

**Latest NDVI**

```bash
curl -k -sS -D -   -H "OCS-APIRequest: true"   -H "requesttoken: <REQUEST_TOKEN>"   -b "<COOKIE_REDACTED>"   "https://127.0.0.1/nextcloud/index.php/apps/weather_apis/api/v1/admin/farms/<FARM_ID>/ndvi/latest?lookback_days=14&max_cloud=30"
```

- Required fields: path `farmId`
- Status: `HTTP 200`
- Sample response (redacted):

```json
{"status":"ok","ok":true,"message":"NDVI latest loaded.","data":{"observation":{...},"lookback_days":14}}
```

**Timeseries NDVI**

```bash
curl -k -sS -D -   -H "OCS-APIRequest: true"   -H "requesttoken: <REQUEST_TOKEN>"   -b "<COOKIE_REDACTED>"   "https://127.0.0.1/nextcloud/index.php/apps/weather_apis/api/v1/admin/farms/<FARM_ID>/ndvi/timeseries?start=2026-01-20&end=2026-01-27&max_cloud=30"
```

- Required fields: query `start`, `end` (ISO)
- Status: `HTTP 200`
- Sample response (redacted):

```json
{"status":"ok","ok":true,"message":"NDVI timeseries loaded.","data":{"observations":[...],"start":"2026-01-20","end":"2026-01-27"}}
```

**Refresh NDVI**

```bash
curl -k -sS -D -   -H "OCS-APIRequest: true"   -H "requesttoken: <REQUEST_TOKEN>"   -b "<COOKIE_REDACTED>"   -X POST   "https://127.0.0.1/nextcloud/index.php/apps/weather_apis/api/v1/admin/farms/<FARM_ID>/ndvi/refresh"
```

- Required fields: path `farmId`
- Status: `HTTP 200` (or `202` depending on DRF)
- Sample response (redacted):

```json
{"status":"ok","ok":true,"message":"NDVI refresh queued.","data":{"job_id":<JOB_ID>}}
```

**Queue raster (body date)**

```bash
curl -k -sS -D -   -H "OCS-APIRequest: true"   -H "requesttoken: <REQUEST_TOKEN>"   -H "Content-Type: application/json"   -b "<COOKIE_REDACTED>"   -X POST -d '{"date":"2026-01-20"}'   "https://127.0.0.1/nextcloud/index.php/apps/weather_apis/api/v1/admin/farms/<FARM_ID>/ndvi/raster/queue"
```

- Required fields: body `date` (ISO)
- Status: `HTTP 200` (or `202` depending on DRF)
- Sample response (redacted):

```json
{"status":"ok","ok":true,"message":"NDVI raster queued.","data":{"job_id":<JOB_ID>}}
```

**Raster PNG**

```bash
curl -k -sS -D -   -H "OCS-APIRequest: true"   -H "requesttoken: <REQUEST_TOKEN>"   -b "<COOKIE_REDACTED>"   "https://127.0.0.1/nextcloud/index.php/apps/weather_apis/api/v1/admin/farms/<FARM_ID>/ndvi/raster.png?date=2026-01-20"   -o /tmp/ndvi-raster.png
```

- Required fields: query `date` (ISO)
- Status: `HTTP 200` (Content-Type `image/png`)
- Sample response (redacted): binary PNG

## 4) Evidence snippets (logs)

**Nextcloud log (controller hit + outbound + status):**

```
Weather API admin endpoint hit {"action":"list farms","requestId":"<REQ_ID>","method":"POST","path":"/apps/weather_apis/api/v1/admin/farms/list"}
Weather API admin proxy request {"action":"list farms","requestId":"<REQ_ID>","method":"GET","path":"/api/v1/farms/","queryKeys":["page"]}
Weather API admin proxy response {"action":"list farms","requestId":"<REQ_ID>","method":"GET","path":"/api/v1/farms/","httpStatus":200}
```

**DRF log (correlated by X-Request-ID):**

```
farms request: method=GET path=/api/v1/farms/ status=200 request_id=<REQ_ID>
ndvi request: method=GET path=/api/v1/farms/<FARM_ID>/ndvi/timeseries/ status=200 request_id=<REQ_ID>
```

## 5) UI proof checklist

- Refresh shows farm columns/values.
- New farm modal renders inputs (name, is_active, decimal bbox/centroid/area).
- NDVI actions run without date validation errors (ISO dates; MM/DD/YYYY normalized to ISO).
- Farm State card renders `State`, `Mean NDVI`, `Max NDVI`, `Coverage`, `Action`, and `Trend`, with the `action` field visible in the main card and not only in debug JSON.
- Raster PNG displays/downloads.
