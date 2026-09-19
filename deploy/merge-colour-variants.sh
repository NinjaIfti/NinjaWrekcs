#!/usr/bin/env bash
#
# Fold the colour-split Valorant families - RGX, Kuronami, Reaver - into one
# product each, with the colours as variants. Wraps products:merge-variants,
# which does the real work: stock is verified inside a transaction and rolled
# back on mismatch, and source products are deactivated rather than deleted so
# order history lives.
#
# Dry run by default - it prints every plan and changes nothing:
#
#     bash deploy/merge-colour-variants.sh
#
# Apply, once the plans read correctly:
#
#     bash deploy/merge-colour-variants.sh --commit
#
# This is a ONE-TIME catalogue operation, not a migration. Running it twice
# creates a second set of merged products; the sources are already inactive by
# then, so re-running is visible but not destructive. To reverse one family:
#
#     php artisan products:unmerge-variants <merged-id> --ids=<source ids> --commit
#
# Ids are from the 2026-09-19 catalogue. Expected total stock carried: 16 units
# across 19 source products, becoming 7 products.

set -euo pipefail

cd "$(dirname "$0")/.."

COMMIT=""
if [[ "${1:-}" == "--commit" ]]; then
    COMMIT="--commit"
    echo "APPLYING the merges. Take a database backup first if you have not:"
    echo "  mysqldump --no-tablespaces -u laravel_user laravel_db > ~/backup-\$(date +%F).sql"
    echo ""
    read -rp "Continue? [y/N] " reply
    [[ "$reply" == "y" || "$reply" == "Y" ]] || { echo "Aborted."; exit 1; }
else
    echo "DRY RUN - nothing will change. Re-run with --commit to apply."
fi

merge() {
    local name="$1" ids="$2" names="$3"
    echo ""
    echo "=============================================================="
    echo " $name"
    echo "=============================================================="
    php artisan products:merge-variants "$name" --ids="$ids" --names="$names" $COMMIT
}

#     merged product name          source ids     variant names          stock
merge "RGX Butterfly"              "2,4,5"        "Red,Green,Blue"       #  0
merge "RGX Dagger"                 "12,13,14"     "Red,Blue,Green"       #  0
merge "RGX Butterfly and Blade"    "120,121"      "Red,Blue"             #  5
merge "Kuronami 7cm"               "15,16,43"     "Red,Purple,Grey"      #  8
merge "Kuronami 22cm"              "93,96"        "White,Black"          #  0
merge "Reaver Krambit 17cm"        "70,71,72,73"  "Purple,Blood Red,Silver Shadow,Green Shadow"  # 1
merge "Reaver Vandal"              "118,119"      "Purple,White"         #  2

# Deferred, not merged here:
#   CSGO Butterfly      101,102,103,104  - waiting on whether the CSGO "Finish"
#                                          series (81-92, 135) is the same knife
#   Singularity 22cm    122,123
#   Recon Butterfly     76,134

echo ""
if [[ -n "$COMMIT" ]]; then
    echo "Done. Check the storefront - the merged products should show a colour"
    echo "picker, and the old per-colour listings should be gone from the shop."
else
    echo "Dry run complete. Nothing changed."
fi
