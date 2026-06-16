# Nextcloud Server (local checkout) + custom app(s)

This directory is a **Nextcloud server checkout** used for local development and customization.

Most of the code here is upstream Nextcloud (core + bundled apps). The custom work in this checkout lives under `apps/`.

## What’s customized in this checkout

Custom / local apps you currently have in `apps/`:

- `apps/farm_intelligence_platform` — custom Nextcloud app that integrates with a Django Farm Intelligence Platform (DRF). Admin-only integration foundations are implemented (settings UI, config/validation, HMAC signing/service layer, diagnostics + test connection) plus admin proxy endpoints for farms/NDVI/weather. Docs: `apps/farm_intelligence_platform/README.md`. Contributing: `apps/farm_intelligence_platform/CONTRIBUTING.md`. Agent rules (for Codex/LLMs): `apps/farm_intelligence_platform/AGENTS.md`.

- `apps/context_chat` — upstream Nextcloud Assistant Context Chat app present in this tree. See `apps/context_chat/README.md` for its dependencies (AppAPI, Assistant, Context Chat Backend) and setup details.

> Note: This repository is **not** a standalone “farm management suite”. Current farm/NDVI/weather admin tooling lives inside the `farm_intelligence_platform` app; any additional workflows should be implemented as apps and documented inside their own folders.

## Repo layout (high level)

- `core/`, `lib/`, `resources/` — Nextcloud server core
- `apps/` — bundled apps + your custom apps (including `weather_apis`)
- `config/` — Nextcloud config (including `config.php`)
- `data/` — Nextcloud data directory (logs, file storage, app data)
- `occ` — Nextcloud CLI entrypoint

## Quickstart (local)

From the Nextcloud root (this folder):

### Check app status
```bash
sudo -u www-data php occ app:list
```
### Enable the custom app
```bash
sudo -u www-data php occ app:enable farm_intelligence_platform
```
### Configure the app
Use the Nextcloud Admin settings UI for farm_intelligence_platform (and/or follow the app-level README):
- `apps/farm_intelligence_platform/README.md`

### Development notes (local)
This checkout is typically located at:
- /var/www/html/nextcloud
When developing apps, keep “core Nextcloud” changes minimal and put customization inside apps/<your_app> whenever possible.

### License
Nextcloud server and bundled components in this repository are licensed under the GNU AGPL v3 (see COPYING and LICENSES/ in this repo). Custom app code under apps/ should remain compatible with AGPL requirements.

### Disclaimer
This software is provided “as is”, without warranty of any kind. Use at your own risk.

### Contact
For questions or issues related to the custom app(s), open an issue in your project tracker or contact: rahimranxx8050@gmail.com
