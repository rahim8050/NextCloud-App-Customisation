# Contributing — Weather APIs (`weather_apis`)

This app will integrate Nextcloud with our Django Weather APIs (DRF). It is currently **pre-integration** (docs + tooling only).

All contributions must follow `apps/weather_apis/AGENTS.md`.

## Prerequisites (fresh machine)

Repository facts (source of truth):
- Nextcloud: `32.0.1` (see `version.php`)
- PHP: `>= 8.1` (see root `composer.json`)
- Composer: `2.x` (required)
- Node (only if building/linting JS/CSS): `22.x` (see root `package.json` `engines`)
- npm (only if building/linting JS/CSS): `>= 10.5` (see root `package.json` `engines`)

You also need a working Nextcloud dev environment (web server + DB) so `php occ status` works.

## Quickstart (docs/tooling loop)

### 1) Install app dev tooling

From the app directory:
```bash
cd apps/weather_apis
composer install
```

### 2) Run PHP quality gates

```bash
cd apps/weather_apis
composer run lint:php
composer run cs:check
composer run stan
composer run test
```

Notes:
- `cs:fix` applies formatting (writes files) if `cs:check` fails.
- Tests must be deterministic: never call real networks. Mock `OCP\Http\Client\IClientService`/`IClient`/`IResponse`.
- `stan` is the static-analysis gate for this repo (tooling is configured in this app).

Auto-fix formatting:
```bash
cd apps/weather_apis
composer run cs:fix
```

## Running Nextcloud locally (environment expectation)

This repo does not ship a one-command dev environment. Use your preferred Nextcloud dev setup and validate it first:

```bash
php occ status
```

If `occ` cannot connect to the DB, fix your local Nextcloud setup before working on this app.

## Enabling the app (TODO until app scaffold exists)

The app is not enable-able until it has standard Nextcloud app scaffolding (`appinfo/info.xml`, etc.). When that is added:

```bash
php occ app:enable weather_apis
php occ routes | rg weather_apis || true
```

## Frontend assets (only if this app adds JS/CSS)

If we introduce JS/CSS later (e.g., an admin settings UI), use the repo-wide toolchain (from the repo root):

```bash
cd /path/to/nextcloud
npm ci
```

Then run app-scoped wrappers (from `apps/weather_apis/`):

```bash
cd apps/weather_apis
npm run lint:js
npm run lint:css
npm run build
```

`npm run format` applies auto-fixes (writes files).

## Logs

- Default Nextcloud log file is usually `data/nextcloud.log`, but the source of truth is `config/config.php` (`logfile`).
- Useful command while developing:
  - `tail -f data/nextcloud.log`

## Documentation requirements (enforced by review)

Any change that introduces or changes:
- config keys
- endpoints/routes
- scripts/commands

…must update:
- `apps/weather_apis/README.md` (app-level docs)
- the root `README.md` (or the central docs location used by this repo), if it documents integrations
- `apps/weather_apis/CONTRIBUTING.md` (tooling/commands)

## PR checklist (copy/paste)

- [ ] Read `apps/weather_apis/AGENTS.md` and followed security + integration contracts
- [ ] Ran: `composer run lint:php` (from `apps/weather_apis/`)
- [ ] Ran: `composer run cs:check` (from `apps/weather_apis/`)
- [ ] Ran: `composer run stan` (from `apps/weather_apis/`)
- [ ] Ran: `composer run test` (from `apps/weather_apis/`)
- [ ] If JS/CSS changed: ran `npm run lint:js`, `npm run lint:css`, `npm run build` (from `apps/weather_apis/`)
- [ ] Confirmed: no secrets committed, logged, or returned in responses
- [ ] Updated docs for any config keys/endpoints/scripts (see “Documentation requirements”)

## Definition of done

- All checklist items above are green.
- Security-sensitive changes (HTTP client, auth, settings) include tests or an explicit review note explaining why not (temporary).
