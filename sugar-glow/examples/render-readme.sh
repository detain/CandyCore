#!/usr/bin/env bash
# Render the project README in a few themes for comparison.
#
#   ./examples/render-readme.sh

set -euo pipefail

# Use the local bin if sugarglow isn't on $PATH (i.e. when running
# from a checkout instead of a global composer install).
sugarglow() {
    if command -v sugarglow >/dev/null 2>&1; then
        command sugarglow "$@"
    else
        ./bin/sugarglow "$@"
    fi
}
export -f sugarglow

README="${1:-../README.md}"

for theme in dark dracula tokyo-night pink; do
    echo "═══ $theme ═══"
    sugarglow --theme "$theme" --width 80 "$README" | head -40
    echo
done
