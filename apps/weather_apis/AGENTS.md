# AGENTS.md — Nextcloud Weather APIs app (`weather_apis`)

This file is the operating contract for any automated agent (Codex/LLM) modifying this Nextcloud app.

## 0) Current status (read first)

- This app is in **pre-integration** mode for outbound DRF calls; integration-foundations work is allowed (settings UI, config keys, validators, DI wiring, minimal admin-only controllers/routes, templates + JS, and tests).
- Do **not** add new controllers/services/routes beyond the settings/admin foundations unless the task explicitly requests it.
- Outbound HTTP / DRF calls must remain behind the service layer (`WeatherApiClient`) and must only be introduced when the task explicitly requests it.
- Unknown backend details must be marked as explicit TODOs (endpoint paths, auth header name, payload schema, etc.). Do not guess.

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

### Allowed exception (integration-foundations)
- Admin settings UI may introduce:
  - `lib/Sections/*` (`IIconSection`)
  - `lib/Settings/*` (`ISettings`)
  - A minimal admin-only controller + route to persist config (no outbound HTTP)
  - Templates + JS for the settings page
- Any HTTP to DRF remains forbidden unless explicitly requested and must live only in `WeatherApiClient`.

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
- The only outbound target is the admin-configured `baseUrl`. Never accept a user-provided full URL (scheme/host/port) in any endpoint.

**Client rule**
- All outbound HTTP must go through Nextcloud `OCP\Http\Client\IClientService` (no raw curl, no `file_get_contents`).

**Redirect rule**
- Redirects must be disabled. Do not follow 3xx responses (prevents redirect-to-metadata SSRF).

**Timeout rule**
- Enforce strict timeouts (connect + total). `timeoutSeconds` must be admin-configurable and bounded.

**URL validation rule (at save-time and at request-time)**
- Parse `baseUrl` and reject if:
  - scheme is not `https`
  - URL contains embedded credentials (`user:pass@host`)
  - host is missing
  - host is `localhost`
- Resolve host (`A` + `AAAA`) and reject if any resolved IP is in a blocked range (IPv4/IPv6 loopback, link-local, private, reserved, multicast, documentation ranges, CGNAT).
- If DNS resolution fails, fail closed (do not attempt the request).
- Provide a documented admin-only override for local dev scenarios where HTTPS termination isn’t available:
  - config key `devAllowHttp` (bool, default `false`)
  - config key `allowlistHosts` (string, blank by default; comma or newline separated entries)
  - When the override is `true`, `baseUrl` may be `http`, but only if the host exactly matches an allowlisted entry.
  - Private/reserved IPs remain blocked unless the host is allowlisted while the override flag is enabled.

**Enforcement**
- Unit tests for URL validation, DNS/IP blocking, and redirect/timeout options.
- PR checklist item: “All outbound HTTP is via `WeatherApiClient` + `IClientService` with redirects disabled + timeouts set.”

> TODO (dev ergonomics): Document the allowed hosts/addresses for `devAllowHttp` + `allowlistHosts` so audits know which entries are legitimate dev targets.

### 3.2 Secrets handling

**Storage rule**
- The `apiKey` must be stored encrypted at rest using `OCP\Security\ICrypto` before writing to app config.

**Exposure rule**
- Never return secrets in any API response (including to admins).
- Exception: ONLY the admin credential generation/rotation endpoints may return the newly generated plaintext `hmacSecret` once. These responses must be admin-only, require CSRF/requesttoken + password confirmation, and include `Cache-Control: no-store`.
- Settings UI must not display stored secrets; only allow replacing them.

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

- `baseUrl` (string): required; validated per SSRF rules; no embedded credentials; **HTTPS-only**
- `clientId` (string): required; HMAC client identifier
- `apiKey` (string): optional/required TBD; stored encrypted via `ICrypto`
- `hmacSecret` (string): stored encrypted via `ICrypto`
- `hmacSecretPrevious` (string, optional): previous HMAC secret for rotation grace window
- `hmacSecretPreviousExpiresAt` (int, optional): Unix timestamp for previous secret expiry
- `timeoutSeconds` (int): required; bounded (TODO define bounds; recommend 1–30)
- `devAllowHttp` (bool): default `false`; enables the dev allowlist override described above
- `allowlistHosts` (string): comma or newline separated hosts; when the dev override is enabled, only allowlisted hosts may resolve to private/reserved IPs or use `http`

Legacy snake_case keys (`base_url`, `api_key`, `hmac_secret`, `hmac_client_id`, `timeout_seconds`, `dev_allow_insecure_local_http`, `dev_allowlist_hosts`) are accepted on save only and are migrated one-way into the canonical schema.

### 4.2 Backend endpoints (placeholders; do not guess)

Document the Django API contract here and keep it updated. Until confirmed, keep explicit TODOs:
- Health check: `GET {baseUrl}/<TODO:health-path>` → 200 JSON (used by `ping()`)
- Forecast: `GET {baseUrl}/<TODO:forecast-path>?lat={lat}&lon={lon}&date={date?}` → 200 JSON
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

### Gate command contract
- `composer run gate` MUST exist and MUST fail the build on:
  - PHP syntax errors
  - coding-style violations
  - static analysis violations
  - failing tests
- Agents must paste gate output into the PR summary.
## 6) Change workflow for agents

When making changes:
1) Summarize the plan in 3–6 bullets.
2) List files you will touch.
3) Implement.
4) Add/adjust tests.
5) Update docs for any config keys or endpoints.
6) Provide a final summary (what changed + how to run gates).

7) Confirm `composer run gate` passes (or explain why and how to reproduce failures).
