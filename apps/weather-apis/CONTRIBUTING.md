````md
# Contributing — Nextcloud Weather APIs App

This app integrates Nextcloud with our Django Weather APIs.

All contributions must follow the rules in `AGENTS.md`.

## Development rules (must follow)

- No secrets committed. Configuration is via Nextcloud app config (admin settings UI).
- Controllers are thin; services contain integration logic.
- Outbound HTTP uses `OCP\Http\Client\IClientService` (no raw curl).
- Do not log tokens/API keys/Authorization headers.

## Local setup

### 1) Place the app in Nextcloud

Install this app into your Nextcloud instance under:

- `nextcloud/apps/<appname>/`

Enable it:

- `php occ app:enable <appname>`

If you add routes/controllers, verify routes are recognized:

- `php occ routes | grep <appname>`

### 2) Configure the app (admin)

Use the app’s Admin Settings page to set:

- Weather API base URL
- Weather API key (if used)

Do not hardcode these values in source code.

## Quality gates (required)

### A) PHP gates (required for any PHP change)

This project must have the following **composer scripts**.

- If they already exist, use them.
- If they do not exist, add them to `composer.json` and keep them stable.

Required scripts:

- `composer run lint:php`  
  Runs PHP syntax lint on relevant files (at minimum).
- `composer run cs:check`  
  Runs the formatter in check/dry-run mode (no writes).
- `composer run cs:fix`  
  Applies formatting.
- `composer run stan`  
  Runs static analysis (PHPStan/Larastan/Psalm depending on repo tooling).
- `composer run test`  
  Runs PHPUnit tests.

Expected usage:

```bash
composer install
composer run lint:php
composer run cs:check
composer run stan
composer run test
````

### B) JS/CSS gates (required only if JS/CSS exists)

If this app includes JS/CSS assets (settings UI, Vue, etc.), this project must have:

* `npm run lint` (ESLint)
* `npm run format:check` (Prettier check)
* `npm run format:write` (Prettier write)
* `npm run stylelint` (Stylelint)
* `npm run build` (build assets)

Expected usage:

```bash
npm ci
npm run lint
npm run format:check
npm run stylelint
npm run build
```

> If your repo uses `pnpm` or `yarn`, use the repo standard and keep script names consistent.

## Adding or updating scripts (do not guess)

When introducing new tooling or scripts:

1. First check if the repository already has preferred tools and scripts.
2. If tools exist but scripts are missing, add the **minimum scripts** to `composer.json` / `package.json`.
3. Update this `CONTRIBUTING.md` to match the exact scripts present.

## Testing guidelines

* Tests must be deterministic: no real network calls.
* Mock Nextcloud HttpClient usage in service tests.
* Prefer testing the service layer; keep controller tests small.

## Documentation requirements

Any change that introduces:

* new config keys
* new endpoints/routes
* new scripts/commands

…must update:

* the app README (inside the app directory)
* the root README (if it documents integrations)
* this `CONTRIBUTING.md` (for scripts/commands)

## Pull request checklist

Before opening a PR:

* [ ] Read `AGENTS.md`
* [ ] Ran PHP gates (`lint:php`, `cs:check`, `stan`, `test`)
* [ ] Ran JS/CSS gates if applicable (`lint`, `format:check`, `stylelint`, `build`)
* [ ] Updated docs for config keys/endpoints/scripts
* [ ] Confirmed no secrets are committed

## Commit discipline

* Keep commits small and descriptive.
* Avoid mixing refactors with feature changes unless necessary.

```
::contentReference[oaicite:0]{index=0}
```
