# AGENTS.md — Nextcloud Weather APIs App

This file is the operating contract for any automated agent (Codex/LLM) modifying this Nextcloud app.
Read it fully before making changes.

## 1) Scope and boundaries

### In scope
- The Nextcloud app code under this app directory only (e.g., `apps/<appname>/`).
- AppFramework controllers, services, settings UI, routes, and app config keys.
- Integration code that calls our Django Weather APIs.

### Out of scope (do not do)
- Do not modify Nextcloud core or other apps.
- Do not introduce new external services or background jobs unless explicitly requested.
- Do not add large dependencies without justification and a summary in the change notes.

## 2) Engineering principles (boring on purpose)

- **Minimal diff:** change the smallest amount of code needed.
- **Layering:** Controllers handle HTTP/JSON only. Services handle business logic and external HTTP calls.
- **Determinism:** Tests must not hit real networks or real SMTP.
- **Config over hardcode:** URLs, keys, and toggles live in Nextcloud app config (IConfig), not source code.
- **Observability:** log errors safely (no tokens, no API keys, no full Authorization headers).

## 3) Security rules (non-negotiable)

### SSRF / outbound HTTP safety
- All outbound HTTP must go through Nextcloud `OCP\Http\Client\IClientService` (no raw curl).
- Set strict timeouts (connect + request).
- Do not allow arbitrary URLs from user input to be fetched.
- Base URL for our Django API must be an **admin-configured app setting**.
- Validate base URL:
  - must be HTTPS in production deployments (allow HTTP only for localhost/dev if explicitly enabled)
  - no embedded credentials
  - avoid link-local / metadata endpoints (if allow/deny logic is introduced, default to safe restrictions)

### Secrets handling
- Never commit API keys, tokens, SMTP credentials, or secrets.
- Store secrets in Nextcloud app config and restrict settings UI to admins.
- Never echo secrets back in API responses.

### Input validation
- Validate query params and JSON bodies in controllers before passing to services.
- Fail closed: reject malformed inputs with stable error responses.

### Auth and permissions
- Decide per endpoint: anonymous vs authenticated Nextcloud user.
- Default to requiring an authenticated Nextcloud session unless explicitly public.

## 4) Architecture conventions

Follow existing repo conventions first. If none exist, use:
- `appinfo/` for `info.xml`, `routes.php`
- `lib/Controller/` for controllers
- `lib/Service/` for external API clients and business logic
- `lib/Settings/` for admin settings
- `templates/` and `js/` only if needed for settings UI

### Service contract
Create a single client service (example: `WeatherApiClient`) that:
- reads base URL + API key from app config
- exposes methods like `ping()`, `forecast(...)`, etc.
- normalizes errors (timeouts, non-2xx, JSON parse errors) into safe exceptions/results

## 5) Configuration keys and documentation

- Any config key added MUST be documented in:
  1) the app README (inside the app directory), and
  2) the root project README (or central docs location), if it exists.
- Config keys must be stable and backwards compatible.
- If a key is renamed (avoid), provide a migration path and support old/new for at least one release.

## 6) Tooling + quality gates (PHP + JS) — no guessing

### Discover existing tooling first (required)
Before adding tooling, inspect and follow what already exists:
- `composer.json` scripts and dev dependencies
- `package.json` scripts and dev dependencies (if JS exists)
- `.github/workflows/*` CI expectations
- repo docs describing lint/test commands

If tooling already exists, USE IT. Do not replace it.

### PHP minimum gates (required)
All changes must satisfy:
- Syntax lint (at least): `php -l` for changed PHP files
- Code style/formatter: use repo tool (examples: Pint, php-cs-fixer)
- Static analysis: use repo tool (examples: PHPStan/Larastan, Psalm)
- Unit tests: use existing PHPUnit setup if present

If scripts exist, use `composer run <script>`.
If scripts do not exist but tools are present, create minimal scripts in `composer.json` WITHOUT changing the tool choice,
and document them in `CONTRIBUTING.md`.

### Frontend minimum gates (only if JS/CSS exists)
If this app has JS/CSS (settings UI, Vue, etc.), enforce:
- ESLint (JS/TS)
- Prettier (format)
- Stylelint (CSS)

If scripts exist, use `npm run <script>` (or `pnpm`/`yarn` if repo standard).
If scripts/tools do not exist but frontend assets are introduced, add minimal scripts
to run ESLint/Prettier/Stylelint and document them in `CONTRIBUTING.md`.

## 7) HTTP integration requirements (Django Weather APIs)

When calling our Django API:
- Base URL must be configurable (admin settings).
- Prefer API key auth if backend supports it; never store user passwords.
- Set timeouts and handle retries carefully (avoid retry storms).
- Map backend errors to stable Nextcloud JSON responses.
- Never expose backend stack traces in responses.

## 8) Change workflow for agents

When making changes:
1) Summarize the plan in 3–6 bullets.
2) List files you will touch.
3) Implement.
4) Add/adjust tests.
5) Update docs for any config keys or endpoints.
6) Provide a final summary:
   - what changed
   - how to configure
   - how to run lint/format/static-analysis/tests (per `CONTRIBUTING.md`)

If uncertain about repo conventions, DO NOT guess:
- inspect existing patterns and follow them
- if still unclear, leave a brief TODO with justification

## 9) Release hygiene

- Keep endpoints backwards compatible when possible.
- Avoid breaking config keys.
- Add migration notes if behavior changes.
