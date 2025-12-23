# AGENTS.md — Nextcloud Weather APIs app (`weather_apis`)

This file is the operating contract for any automated agent (Codex/LLM) modifying this Nextcloud app.

## 0) Current status (read first)

- This app is in **pre-integration** mode: docs + tooling only. Do **not** add controllers/services/routes or Django calls unless the task explicitly requests it.
- Unknown backend details must be marked as explicit TODOs (endpoint paths, auth header name, payload schema, etc.).

## 1) Scope and boundaries

### In scope
- Files under `apps/weather_apis/` only.
- AppFramework code, settings UI, routes, services, tests, and docs for this app (when integration begins).
- Tooling/config needed to make the rules enforceable (composer scripts, linters, static analysis, test scaffolding).

### Out of scope (do not do)
- Do not modify Nextcloud core or other apps.
- Do not introduce new external services or background jobs unless explicitly requested.
- Do not add large dependencies without justification and a summary in change notes.

## 2) Architecture contract (must)

### Layering
- **Controllers**: HTTP + JSON only (input validation, auth/permissions, status codes, response shaping). No business logic, no outbound HTTP.
- **Service layer**: All business logic and all outbound HTTP to the Django backend lives here (single client: `WeatherApiClient`).
- **Settings**: Admin-only settings UI writes config keys; the service reads config keys at runtime.

### File layout (when code exists)
- `appinfo/` (`info.xml`, `routes.php`)
- `lib/Controller/` (thin controllers)
- `lib/Service/` (`WeatherApiClient`, DTOs, error mapping)
- `lib/Settings/` (admin settings)

### Enforceability
- Put integration logic behind one service API so tests can mock it.
- Add unit tests for the service; keep controller tests minimal and deterministic.

## 3) Security contract (non-negotiable)

Every “rule” here must have an enforcement path: unit tests, static analysis, and/or an explicit PR checklist item.

### 3.1 Outbound HTTP / SSRF defenses

**Routing rule**
- The only outbound target is the admin-configured `base_url`. Never accept a user-provided full URL (scheme/host/port) in any endpoint.

**Client rule**
- All outbound HTTP must go through Nextcloud `OCP\Http\Client\IClientService` (no raw curl, no `file_get_contents`).

**Redirect rule**
- Redirects must be disabled. Do not follow 3xx responses (prevents redirect-to-metadata SSRF).

**Timeout rule**
- Enforce strict timeouts (connect + total). `timeout_seconds` must be admin-configurable and bounded.

**URL validation rule (at save-time and at request-time)**
- Parse `base_url` and reject if:
  - scheme is not `https`
  - URL contains embedded credentials (`user:pass@host`)
  - host is missing
  - host is `localhost`
- Resolve host (`A` + `AAAA`) and reject if any resolved IP is in a blocked range (IPv4/IPv6 loopback, link-local, private, reserved, multicast, documentation ranges, CGNAT).
- If DNS resolution fails, fail closed (do not attempt the request).

**Enforcement**
- Unit tests for URL validation, DNS/IP blocking, and redirect/timeout options.
- PR checklist item: “All outbound HTTP is via `WeatherApiClient` + `IClientService` with redirects disabled + timeouts set.”

> TODO (dev ergonomics): If local HTTP is required for dev, introduce an explicit admin-only toggle (default false) and limit it to `localhost`/`127.0.0.1`/`[::1]`.

### 3.2 Secrets handling

**Storage rule**
- The `api_key` must be stored encrypted at rest using `OCP\Security\ICrypto` before writing to app config.

**Exposure rule**
- Never return secrets in any API response (including to admins). Settings UI must not display stored secrets; only allow replacing them.

**Logging rule**
- Never log:
  - API keys/tokens
  - full `Authorization` headers
  - backend response bodies that could contain secrets
- Log only sanitized metadata: HTTP method, path (no query), status code, duration, request/correlation id, and a short error code.

**Enforcement**
- Unit test(s) for encryption-at-rest (stored value != plaintext).
- Unit test(s) for log redaction helper (no secret substrings).
- PR checklist item: “No secrets are logged or returned.”

### 3.3 Auth boundaries (Nextcloud-side)

**Settings endpoints**
- Must be admin-only.
- Must use Nextcloud PHP attributes for protection (documented here; apply when code exists):
  - `AdminRequired` / `AuthorizedAdminSetting` for admin-only settings actions
  - `PasswordConfirmationRequired` for changing the API key / base URL

**Weather proxy endpoints**
- Default to authenticated Nextcloud sessions.
- Anonymous/public access must be explicitly requested, documented, and threat-modeled.

**Enforcement**
- Controller attributes + unit/integration tests for permissions.
- PR checklist item: “All settings writes are admin-only + password-confirmed.”

### 3.4 Input validation (proxy endpoints)

If/when proxy endpoints accept location parameters, validate strictly in controllers before calling services:
- `lat`: required float, range `[-90, 90]`
- `lon`: required float, range `[-180, 180]`
- `date`: optional, strict `YYYY-MM-DD` (reject ambiguous formats)
- `units`: optional enum (TODO define allowed values)
- Reject unknown parameters; limit string lengths; fail closed with stable error responses.

**Enforcement**
- Unit tests for validation (edge cases, invalid inputs).

### 3.5 Error normalization (no leakage)

Never expose backend stack traces, internal URLs, or raw backend payloads.

**Normalized error schema (Nextcloud API responses)**
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

**Enforcement**
- Unit tests for error mapping (timeouts, non-2xx, JSON parse errors).
- PR checklist item: “Backend errors are mapped to stable codes; no raw backend errors leak.”

### 3.6 Dependency hygiene

- Keep dependencies pinned (lock files committed where used).
- Avoid adding new dependencies unless needed; justify in PR description.
- Run `composer audit` / `npm audit` when adding or updating dependencies (expect false positives; document triage).

## 4) Integration handshake contract (Nextcloud ↔ Django DRF)

### 4.1 Config keys (must exist)

All keys are scoped to app id `weather_apis` in Nextcloud app config.

- `base_url` (string): required; validated per SSRF rules; no embedded credentials; **HTTPS-only**
- `api_key` (string): optional/required TBD; stored encrypted via `ICrypto`
- `timeout_seconds` (int): required; bounded (TODO define bounds; recommend 1–30)

### 4.2 Backend endpoints (placeholders; do not guess)

Document the Django API contract here and keep it updated. Until confirmed, keep explicit TODOs:
- Health check: `GET {base_url}/<TODO:health-path>` → 200 JSON (used by `ping()`)
- Forecast: `GET {base_url}/<TODO:forecast-path>?lat={lat}&lon={lon}&date={date?}` → 200 JSON
- Auth header: `<TODO: header name + format>` (e.g., `Authorization: Bearer …` or `X-Api-Key: …`)

### 4.3 Normalized success shape (Nextcloud API responses)

Keep controller responses stable and simple:
```json
{ "status": "ok", "data": {} }
```

### 4.4 Observability / correlation ids

- Use Nextcloud `ILogger` with structured context (never secrets).
- Propagate a correlation id to the backend:
  - Prefer incoming `X-Request-Id` if present, else generate a UUID.
  - Send it as `X-Request-Id` (or TODO if backend expects a different header).
- Include `requestId` in error responses (and optionally in success responses if requested later).

## 5) Tooling and review gates (must)

All PRs for this app must satisfy:
- PHP lint, style check, static analysis, and unit tests via composer scripts documented in `apps/weather_apis/CONTRIBUTING.md`.
- No real network calls in tests; mock `IClientService`/`IClient`/`IResponse`.
- Any new config keys/endpoints must update app docs and root docs (see `CONTRIBUTING.md`).

## 6) Change workflow for agents

When making changes:
1) Summarize the plan in 3–6 bullets.
2) List files you will touch.
3) Implement.
4) Add/adjust tests.
5) Update docs for any config keys or endpoints.
6) Provide a final summary (what changed + how to run gates).
