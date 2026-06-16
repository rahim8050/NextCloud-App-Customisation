# Contributing — Farm Intelligence Platform (`farm_intelligence_platform`)

This app will integrate Nextcloud with our Django Farm Intelligence Platform (DRF). It is currently **pre-integration** (docs + tooling only).

All contributions must follow `apps/farm_intelligence_platform/AGENTS.md`.

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
cd apps/farm_intelligence_platform
composer install
```

### 2) Run PHP quality gates

```bash
cd apps/farm_intelligence_platform
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
cd apps/farm_intelligence_platform
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
php occ app:enable farm_intelligence_platform
php occ routes | rg farm_intelligence_platform || true
```

## Frontend assets (only if this app adds JS/CSS)

If we introduce JS/CSS later (e.g., an admin settings UI), use the repo-wide toolchain (from the repo root):

```bash
cd /path/to/nextcloud
npm ci
```

Then run app-scoped wrappers (from `apps/farm_intelligence_platform/`):

```bash
cd apps/farm_intelligence_platform
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
- `apps/farm_intelligence_platform/README.md` (app-level docs)
- the root `README.md` (or the central docs location used by this repo), if it documents integrations
- `apps/farm_intelligence_platform/CONTRIBUTING.md` (tooling/commands)

## PR checklist (copy/paste)

- [ ] Read `apps/farm_intelligence_platform/AGENTS.md` and followed security + integration contracts
- [ ] Ran: `composer run lint:php` (from `apps/farm_intelligence_platform/`)
- [ ] Ran: `composer run cs:check` (from `apps/farm_intelligence_platform/`)
- [ ] Ran: `composer run stan` (from `apps/farm_intelligence_platform/`)
- [ ] Ran: `composer run test` (from `apps/farm_intelligence_platform/`)
- [ ] If JS/CSS changed: ran `npm run lint:js`, `npm run lint:css`, `npm run build` (from `apps/farm_intelligence_platform/`)
- [ ] Confirmed: no secrets committed, logged, or returned in responses
- [ ] Updated docs for any config keys/endpoints/scripts (see “Documentation requirements”)

## Definition of done

- All checklist items above are green.
- Security-sensitive changes (HTTP client, auth, settings) include tests or an explicit review note explaining why not (temporary).
