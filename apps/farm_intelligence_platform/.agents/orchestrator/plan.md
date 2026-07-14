# Phase 2 Development Plan

## Objectives
- Integrate Scene Classification Layer (SCL) and QA_PIXEL masking.
- Integrate the foundational NDVI equation `(NIR - RED) / (NIR + RED)`.
- Apply strict numerical safeguards (NaN clamping and division-by-zero prevention).
- Decompress raw TIFF tile byte streams into contiguous 1D float/integer arrays.

## Verification Steps
1. Verify the NDVI formula edge cases using unit tests (e.g. division-by-zero returns NaN).
2. Verify cloud/shadow masking returns NaN outputs.
3. Fix test_too_many_tiles_limit by adjusting mock TIFF scale to 500.0 and northing to 4,500,000.0 to ensure correct grid intersection.
4. Verify all tests pass cleanly.
