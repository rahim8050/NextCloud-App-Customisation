# Farms Golden Vector (DRF + Nextcloud)

> Status: **BLOCKED (Nextcloud auth required)**
> 
> This run verified the DRF schema + direct DRF CRUD. Nextcloud proxy calls are currently blocked by missing admin auth (HTTP 401). See **Blocked items** below for exact errors and required next steps.

## Environment assumptions (from live responses)

- DRF base URL: `http://127.0.0.1:8001`
- Nextcloud base URL (HTTPS): `https://127.0.0.1/nextcloud`
- Nextcloud webroot: `/nextcloud` (from `overwritewebroot`)

## A) DRF OpenAPI schema (live)

### Paths (from `GET /api/schema/?format=json`)

- `GET /api/v1/farms/` → list
- `POST /api/v1/farms/` → create
- `GET /api/v1/farms/{id}/` → retrieve
- `PATCH /api/v1/farms/{id}/` → update (partial)
- `PUT /api/v1/farms/{id}/` → update (full)

### List response shape

Schema shows **array** response (not paginated):

```json
{
  "type": "array",
  "items": { "$ref": "#/components/schemas/Farm" }
}
```

### Create + update request bodies

- `POST /api/v1/farms/` requestBody: `Farm`
- `PUT /api/v1/farms/{id}/` requestBody: `Farm`
- `PATCH /api/v1/farms/{id}/` requestBody: `PatchedFarm`
- Content types: `application/json`, `application/x-www-form-urlencoded`, `multipart/form-data`

### Writable fields (Farm schema, excluding readOnly)

From `components.schemas.Farm`:

- `name` (string, maxLength 120)
- `centroid_lat` (string decimal, nullable)
- `centroid_lon` (string decimal, nullable)
- `bbox_south` (string decimal, nullable)
- `bbox_west` (string decimal, nullable)
- `bbox_north` (string decimal, nullable)
- `bbox_east` (string decimal, nullable)
- `area_ha` (string decimal, nullable)
- `is_active` (boolean)

Read-only fields (not for create/update): `id`, `slug`, `created_at`, `updated_at`.

## B) DRF direct curls (authenticated via X-API-Key)

> Replace `<API_KEY>` with a valid key. Do **not** paste secrets in logs.

### 1) List farms (initial)

```bash
curl -sS -D - -H "X-API-Key: <API_KEY>" \
  http://127.0.0.1:8001/api/v1/farms/
```

Observed response (this run):

```
HTTP/1.1 200 OK
Content-Type: application/json

[]
```

### 2) Create farm (minimal)

```bash
curl -sS -D - -H "X-API-Key: <API_KEY>" \
  -H "Content-Type: application/json" \
  -d '{"name":"Codex Farm A","is_active":true}' \
  http://127.0.0.1:8001/api/v1/farms/
```

Observed response (this run):

```
HTTP/1.1 201 Created
Content-Type: application/json

{"id":20,"name":"Codex Farm A","slug":"codex-farm-a","centroid_lat":null,"centroid_lon":null,"bbox_south":null,"bbox_west":null,"bbox_north":null,"bbox_east":null,"area_ha":null,"is_active":true,"created_at":"2026-01-24T11:41:46.814030+03:00","updated_at":"2026-01-24T11:41:46.814142+03:00"}
```

### 3) Retrieve farm

```bash
curl -sS -D - -H "X-API-Key: <API_KEY>" \
  http://127.0.0.1:8001/api/v1/farms/<FARM_ID>/
```

Observed response (this run, `id=20`):

```
HTTP/1.1 200 OK
Content-Type: application/json

{"id":20,"name":"Codex Farm A","slug":"codex-farm-a","centroid_lat":null,"centroid_lon":null,"bbox_south":null,"bbox_west":null,"bbox_north":null,"bbox_east":null,"area_ha":null,"is_active":true,"created_at":"2026-01-24T11:41:46.814030+03:00","updated_at":"2026-01-24T11:41:46.814142+03:00"}
```

### 4) Update farm (PATCH)

```bash
curl -sS -D - -H "X-API-Key: <API_KEY>" \
  -H "Content-Type: application/json" \
  -X PATCH -d '{"name":"Codex Farm A Updated"}' \
  http://127.0.0.1:8001/api/v1/farms/<FARM_ID>/
```

Observed response (this run, `id=20`):

```
HTTP/1.1 200 OK
Content-Type: application/json

{"id":20,"name":"Codex Farm A Updated","slug":"codex-farm-a","centroid_lat":null,"centroid_lon":null,"bbox_south":null,"bbox_west":null,"bbox_north":null,"bbox_east":null,"area_ha":null,"is_active":true,"created_at":"2026-01-24T11:41:46.814030+03:00","updated_at":"2026-01-24T11:43:25.556964+03:00"}
```

### 5) List farms (post-update)

```bash
curl -sS -D - -H "X-API-Key: <API_KEY>" \
  http://127.0.0.1:8001/api/v1/farms/
```

Observed response (this run):

```
HTTP/1.1 200 OK
Content-Type: application/json

[{"id":20,"name":"Codex Farm A Updated","slug":"codex-farm-a","centroid_lat":null,"centroid_lon":null,"bbox_south":null,"bbox_west":null,"bbox_north":null,"bbox_east":null,"area_ha":null,"is_active":true,"created_at":"2026-01-24T11:41:46.814030+03:00","updated_at":"2026-01-24T11:43:25.556964+03:00"}]
```

## C) Nextcloud proxy curls (authenticated)

> **Blocked:** admin auth is required. Current unauthenticated responses return HTTP 401 with `{"message":"Current user is not logged in"}`.
>
> Please provide one of:
> - An admin app password (preferred), or
> - A sanitized `Copy as cURL` from browser DevTools (redact cookies/tokens), or
> - Permission to run `occ` as `www-data` to create an admin app password.

### 1) Schema endpoint

```bash
curl -k -sS https://127.0.0.1/nextcloud/index.php/apps/weather_apis/api/v1/admin/farms/schema
```

Observed response (unauthenticated):

```
HTTP/2 401
{"message":"Current user is not logged in"}
```

### 2) List endpoint

```bash
curl -k -sS https://127.0.0.1/nextcloud/index.php/apps/weather_apis/api/v1/admin/farms/list
```

Observed response (unauthenticated):

```
HTTP/2 401
{"message":"Current user is not logged in"}
```

### 3) Create endpoint (blocked)

```bash
curl -k -sS -X POST \
  -H "Content-Type: application/json" \
  -d '{"name":"Codex Farm A"}' \
  https://127.0.0.1/nextcloud/index.php/apps/weather_apis/api/v1/admin/farms/create
```

Expected (authenticated): `status: ok`, created `id`.

### 4) Patch endpoint (blocked)

```bash
curl -k -sS -X PATCH \
  -H "Content-Type: application/json" \
  -d '{"name":"Codex Farm A Updated"}' \
  https://127.0.0.1/nextcloud/index.php/apps/weather_apis/api/v1/admin/farms/<ID>
```

Expected (authenticated): `status: ok`.

## D) Proof artifacts (logs)

### DRF logs (django.log)

Recent lines show the farms requests (paths + methods):

```
api_key.auth.success path=/api/v1/farms/ method=GET ip=127.0.0.1 ua=curl/8.5.0 user_id=4 key_id=...
api_key.auth.success path=/api/v1/farms/ method=POST ip=127.0.0.1 ua=curl/8.5.0 user_id=4 key_id=...
api_key.auth.success path=/api/v1/farms/20/ method=GET ip=127.0.0.1 ua=curl/8.5.0 user_id=4 key_id=...
api_key.auth.success path=/api/v1/farms/20/ method=PATCH ip=127.0.0.1 ua=curl/8.5.0 user_id=4 key_id=...
api_key.auth.success path=/api/v1/farms/ method=GET ip=127.0.0.1 ua=curl/8.5.0 user_id=4 key_id=...
```

HTTP status codes are captured in the curl headers above (201/200).

### Nextcloud logs (nextcloud.log)

Unauthenticated proxy attempts (blocked):

```
{"reqId":"FG56lDJCpAy0cvYzCtw6","app":"weather_apis","method":"GET","url":"/nextcloud/index.php/apps/weather_apis/api/v1/admin/farms/schema","message":"Current user is not logged in"}
{"reqId":"UVkhciUWYJR9Jq6LcZJf","app":"weather_apis","method":"GET","url":"/nextcloud/index.php/apps/weather_apis/api/v1/admin/farms/list","message":"Current user is not logged in"}
```

## E) UI proof steps (blocked pending auth)

1) Open Nextcloud admin settings → Weather APIs → Farms
2) Click **Refresh** → table should show columns + row values
3) Click **Create** → modal renders inputs from schema
4) Save new farm → row appears in table
5) Edit farm → update persists, table re-renders

**Blocked:** Nextcloud admin authentication required to access the schema + proxy endpoints.

## Blocked items + required next steps

- `occ router:list` fails due to config permissions (needs to run as `www-data` owner):
  - Error: `Cannot write into "config" directory!`
  - Fix: run `sudo -u www-data php /var/www/html/nextcloud/occ router:list | rg -i weather_apis | rg -i farms`

- Nextcloud proxy endpoints return 401 (no admin session):
  - Provide admin app password or sanitized DevTools cURL so authenticated calls can be executed.

Once authenticated access is available, re-run **C** and **E** and update this document with live responses.
