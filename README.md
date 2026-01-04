# Nextcloud Server (local checkout) + custom app(s)

This directory is a **Nextcloud server checkout** used for local development and customization.

Most of the code here is upstream Nextcloud (core + bundled apps). The custom work in this checkout lives under `apps/`.

## What’s customized in this checkout

Custom / local apps you currently have in `apps/`:

- `apps/weather_apis` — custom Nextcloud app that integrates with an external Weather APIs backend (Django/DRF).
  - Current state: integration foundations (admin settings + config/validation + signing/service layer). UI → backend calls are being finalized.
  - App docs: `apps/weather_apis/README.md`
  - Agent rules (for Codex/LLMs): `apps/weather_apis/AGENTS.md`

- `apps/context_chat` — local app experiments (present in this tree).

> Note: This repository is **not** a standalone “farm management suite”. If you want farm workflows, they should be implemented as actual Nextcloud apps (and documented inside their own app folders).

## Repo layout (high level)

- `core/`, `lib/`, `resources/` — Nextcloud server core
- `apps/` — bundled apps + your custom apps (including `weather_apis`)
- `occ` — Nextcloud CLI entrypoint

## Quickstart (local)

From the Nextcloud root (this folder):

### Check app status
```bash
sudo -u www-data php occ app:list
```
### Enable the custom app
```bash
sudo -u www-data php occ app:enable weather_apis
```
### Configure the app
Use the Nextcloud Admin settings UI for weather_apis (and/or follow the app-level README):
- apps/weather_apis/README.md

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