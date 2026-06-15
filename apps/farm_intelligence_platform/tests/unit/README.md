# Unit tests (placeholder)

This directory will hold deterministic unit tests for the `farm_intelligence_platform` app.

When integration begins:
- Mock outbound HTTP via `OCP\Http\Client\IClientService` / `IClient` / `IResponse`
- Do not hit real networks in tests
- Prefer testing the service layer; keep controller tests minimal
