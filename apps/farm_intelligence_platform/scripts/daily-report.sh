#!/bin/bash
# Daily report generator for weather_apis (Nextcloud app)
# Usage: ./scripts/daily-report.sh [date]  (defaults to today)

set -euo pipefail

DATE="${1:-$(date +%Y-%m-%d)}"
REPO_DIR="$(cd "$(dirname "$0")/.." && pwd)"
REPORT_DIR="${REPO_DIR}/reports"
mkdir -p "${REPORT_DIR}"

REPORT_FILE="${REPORT_DIR}/daily-${DATE}.md"

echo "# Daily Report — weather_apis (${DATE})" > "${REPORT_FILE}"
echo "" >> "${REPORT_FILE}"
echo "Generated: $(date '+%Y-%m-%d %H:%M:%S %Z')" >> "${REPORT_FILE}"
echo "" >> "${REPORT_FILE}"

cd "${REPO_DIR}"

echo "## Commits" >> "${REPORT_FILE}"
echo "" >> "${REPORT_FILE}"
COMMITS=$(git log --since="${DATE}T00:00:00" --until="${DATE}T23:59:59" --pretty=format:"- %h %s (%an, %cr)" --no-merges 2>/dev/null || true)
if [ -z "${COMMITS}" ]; then
  echo "_No commits_" >> "${REPORT_FILE}"
else
  echo "${COMMITS}" >> "${REPORT_FILE}"
fi
echo "" >> "${REPORT_FILE}"
echo "" >> "${REPORT_FILE}"

echo "## Gate Summary" >> "${REPORT_FILE}"
echo "" >> "${REPORT_FILE}"
echo "Run \`composer run gate\` for full results (lint + cs:check + psalm + tests)" >> "${REPORT_FILE}"
echo "" >> "${REPORT_FILE}"

echo "## Test Summary" >> "${REPORT_FILE}"
echo "" >> "${REPORT_FILE}"
TEST_OUTPUT=$(vendor/bin/phpunit -c tests/phpunit.xml --colors=never 2>&1 | tail -3 || true)
echo "\`\`\`" >> "${REPORT_FILE}"
echo "${TEST_OUTPUT}" >> "${REPORT_FILE}"
echo "\`\`\`" >> "${REPORT_FILE}"
echo "" >> "${REPORT_FILE}"

echo "## File Changes" >> "${REPORT_FILE}"
echo "" >> "${REPORT_FILE}"
git diff --stat --since="${DATE}T00:00:00" --until="${DATE}T23:59:59" -- apps/weather_apis/ >> "${REPORT_FILE}" 2>/dev/null || echo "_No changes_" >> "${REPORT_FILE}"
echo "" >> "${REPORT_FILE}"

echo "Report saved to: ${REPORT_FILE}"
cat "${REPORT_FILE}"
