# Original User Request

## Initial Request — 2026-07-13T12:11:51Z

# Teamwork Project Prompt — Draft

> Status: Ready for launch — awaiting user approval
> Goal: Craft prompt → get user approval → delegate to teamwork_preview

Implement Phase 2 (Core Mathematics & Stability) of the NDVI Rust Microservice. This involves decompressing raw TIFF tile byte streams, parsing the Scene Classification Layer (SCL) for cloud/shadow masking, and integrating the raster algebra with strict numerical safeguards (e.g. NaN clamping).

Working directory: /home/rahim/projects/Farm-Intelligence-Platform/ndvi-service
Integrity mode: development

## Requirements

### R1. Tiff Extraction & Decompression
The service must decompress the raw byte blocks fetched from the COG endpoints into contiguous 1D float/integer arrays representing the pixel data. 

### R2. SCL Cloud & Shadow Masking
The service must support an optional SCL (Scene Classification Layer) or QA_PIXEL URL in the payload. It must parse this layer to identify clouds, shadows, or NoData pixels, and flag them appropriately so they can be masked out of calculations.

### R3. Raster Algebra Integration
Integrate the foundational NDVI equation `(NIR - RED) / (NIR + RED)` into the processing pipeline. Apply strict numerical safeguards to intercept division-by-zero and clamp the output strictly within `[-1.0, 1.0]`. Masked pixels should evaluate to `NaN`.

## Acceptance Criteria

### Testing & Verification
- [ ] Dedicated Rust unit tests are written to verify the NDVI formula edge cases (e.g., denominator = 0.0 results in NaN).
- [ ] Dedicated Rust unit tests verify that pixels marked as cloud/shadow in the SCL layer correctly result in `NaN` outputs.
- [ ] The codebase passes `cargo check` and `cargo test` cleanly.

---
*Next: when approved → delegate via invoke_subagent (see Delegation Protocol)*
