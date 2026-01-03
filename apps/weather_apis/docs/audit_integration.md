# Integration Audit: Nextcloud <-> DRF

## Scope
Audit and hardening for HMAC signing, JWT minting/refresh, settings consistency, SSRF controls, logging, and rate limiting.

## Canonical signing spec (authoritative)
Applies to Nextcloud -> DRF HMAC signing for the integration token bootstrap and Nextcloud HMAC ping.

- Method: uppercase HTTP method (e.g., `GET`, `POST`).
- Path normalization: exact `request.path` with leading slash; trailing slash is significant.
- Query string canonicalization:
  - Parse raw query string with `&`, keep blank values, preserve duplicates.
  - Decode percent-encoding and convert `+` to space.
  - Re-encode with RFC3986-safe characters (`-_.~`), spaces as `%20`.
  - Sort by `(encoded_key, encoded_value)`; rejoin with `&` and no leading `?`.
- Timestamp: unix seconds integer (`X-Timestamp` / `X-NC-TIMESTAMP`).
  - Allowed skew is `NEXTCLOUD_HMAC_MAX_SKEW_SECONDS`.
  - Rejects timestamps that are too old or too far in the future.
- Nonce: unique per request, any non-empty string; replayed values are rejected for `NEXTCLOUD_HMAC_NONCE_TTL_SECONDS`.
- Body hash: SHA-256 hex of raw request bytes.
  - For `GET`, hash the empty body (`""`).
  - Raw bytes are used; no JSON re-serialization.
- Canonical string (newline-delimited, no trailing newline):
  ```
  METHOD
  PATH
  CANONICAL_QUERY
  TIMESTAMP
  NONCE
  BODY_SHA256
  ```
- Signature algorithm: HMAC-SHA256 over canonical string, hex-encoded.
- Header names:
  - Client id: `X-Client-Id` (preferred), `X-NC-CLIENT-ID` (legacy alias).
  - Timestamp: `X-Timestamp` (preferred), `X-NC-TIMESTAMP` (legacy alias).
  - Nonce: `X-Nonce` (preferred), `X-NC-NONCE` (legacy alias).
  - Signature: `X-Signature` (preferred), `X-NC-SIGNATURE` (legacy alias).
  - API key (token bootstrap only): `X-API-Key`.
  - Request id (correlation): `X-Request-Id`.
  - Body hash: not sent as a header; it is included only in the canonical string.

## Findings

1) High risk: Nextcloud canonicalization did not match DRF spec.
- Impact: HMAC signature mismatches on query normalization or method case could break auth or cause false negatives.
- Evidence: `lib/Service/TokenSigner.php` (prior behavior used raw query string and method as provided).

2) Medium risk: Token cache TTL ignored backend `expires_in`.
- Impact: stale tokens could remain cached beyond backend lifetime, causing unnecessary 401s.
- Evidence: `lib/Service/WeatherApiClient.php` cached tokens with a fixed TTL.

3) Medium risk: HMAC failures lacked stable machine-readable error codes.
- Impact: hard to reason about skew vs nonce replay vs unknown client id.
- Evidence: `integrations/permissions.py` raised a generic PermissionDenied message.

4) Medium risk: No per-client rate limiting for HMAC endpoints.
- Impact: per-client abuse could bypass per-user throttles on HMAC-only routes.
- Evidence: `integrations/views.py` did not attach per-client throttles to Nextcloud HMAC views.

5) Low risk: Logging could leak secrets if exception messages include headers or payloads.
- Impact: potential secret exposure in logs if upstream errors include sensitive values.
- Evidence: `lib/Controller/SettingsController.php`, `lib/Controller/WhoamiRequestHandlerTrait.php`, `lib/Service/WeatherApiClient.php`.

## Fixes applied
- Canonical signing aligned with DRF: query normalization + method uppercasing + raw body hash (`lib/Service/TokenSigner.php`, `tests/unit/Service/TokenSignerTest.php`).
- Token cache TTL now respects backend `expires_in`, with small skew buffer; 401 triggers a single retry after clearing cache (`lib/Service/WeatherApiClient.php`, `tests/unit/Service/WeatherApiClientTest.php`).
- HMAC errors now expose stable machine-readable codes (without leaking secrets) (`integrations/hmac.py`, `integrations/permissions.py`).
- Per-client HMAC throttling added with per-IP fallback (`integrations/throttling.py`, `integrations/views.py`, `tests/test_integrations_nextcloud_hmac.py`).
- Log redaction helper added and used in all logging calls that could include sensitive values (`lib/Service/LogSanitizer.php`, `tests/unit/Service/LogSanitizerTest.php`).
- Additional SSRF tests added for devAllowHttp allowlist enforcement (`tests/unit/UrlValidatorTest.php`).

## Remaining TODOs
- None.

## How to test

Manual (end-to-end)
1) Configure Nextcloud Weather APIs settings with `baseUrl`, `clientId`, `apiKey`, `hmacSecret`, and `timeoutSeconds`.
2) Call `POST {baseUrl}/api/v1/integration/token/` with HMAC headers and `X-API-Key`; verify a short-lived access token is returned.
3) Call `GET {baseUrl}/api/v1/integration/whoami/` with `Authorization: Bearer <token>` and verify identity response.
4) Repeat a signed request with the same nonce to confirm replay protection (expect 403 with `errors.code = "nonce_replay"`).
5) Send two HMAC ping requests in quick succession with the same client id to confirm 429 and `Retry-After` header.

Automated
- Nextcloud app:
  - `composer run lint`
  - `composer run cs:check`
  - `composer run psalm`
  - `composer run test`
- DRF backend:
  - `ruff check .`
  - `ruff format .`
  - `mypy .`
  - `bandit -c pyproject.toml -r .`
  - `pytest`
