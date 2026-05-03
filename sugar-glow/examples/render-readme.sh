#!/usr/bin/env bash
# Render the project README in a few themes for comparison.
#
#   ./examples/render-readme.sh

set -euo pipefail

# Resolve `sugarglow` once up-front. `command -v` inside the function
# would match the function itself and recurse / fall through to a
# binary lookup that fails when running from a checkout without a
# global composer install. Stash an absolute path instead.
if [ -x "$(dirname "${BASH_SOURCE[0]}")/../bin/sugarglow" ]; then
    SUGARGLOW_BIN="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)/bin/sugarglow"
else
    SUGARGLOW_BIN="$(type -P sugarglow || true)"
    if [ -z "$SUGARGLOW_BIN" ]; then
        echo "sugarglow: not found on PATH and ./bin/sugarglow missing" >&2
        exit 1
    fi
fi
export SUGARGLOW_BIN
sugarglow() { "$SUGARGLOW_BIN" "$@"; }
export -f sugarglow

README="${1:-../README.md}"

for theme in dark dracula tokyo-night pink; do
    echo "═══ $theme ═══"
    sugarglow --theme "$theme" --width 80 "$README" | head -40
    echo
done
