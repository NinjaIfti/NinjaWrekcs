#!/usr/bin/env bash
#
# Batch 2: fold the remaining colour-split families into one product each.
# Wraps products:merge-variants, which does the real work: stock is verified
# inside a transaction and rolled back on mismatch, and source products are
# deactivated rather than deleted so order history lives.
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
# creates a second set of merged products. To reverse one family:
#
#     php artisan products:unmerge-variants <merged-id> --ids=<source ids> --commit
#
# Batch 1 (already merged 2026-09-19, do NOT re-run): RGX Butterfly 143,
# RGX Dagger 144, RGX Butterfly and Blade 145, Kuronami 7cm 146,
# Kuronami 22cm 147, Reaver Krambit 17cm 148, Reaver Vandal 149.
#
# Ids are from the 2026-09-19 catalogue. Expected total stock carried in this
# batch: 20 units across 24 source products, becoming 4 products.

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

# The Butterfly series (1,500), the Gamma Doppler (1,300) and the whole Finish
# series (950) are one knife - each variant keeps its own price. Source 101
# leads so the merged product takes its category, description and images.
# NOTE: ids 81 and 88 are both "CSGO Gradient Finish", so two swatches will
# read the same. Deactivate one variant in admin afterwards.
merge "CSGO Butterfly" \
      "101,102,103,104,135,81,82,83,84,85,86,87,88,89,90,91,92" \
      "Blue Shadow,Red Doppler,Red Shadow,Green Shadow,Gamma Doppler,Gradient Finish,Black Claw,White Chromium,Rainbow Chromium,Black Knife with Hole,Old School Finish,Celestial Finish,Gradient Finish,Simov Finish,Shark Finish,Sakura Finish,Blue Thunder Finish"

# Trainers are a separate product from the real knife.
merge "CSGO Butterfly Trainer" "136,137,138" "Blue Wave,Majesty Forest,Tanto"   # 2 units

merge "Singularity 22cm"       "122,123"     "Yellow,Brown"                     # 4 units

merge "Recon Butterfly"        "76,134"      "Green,Red"                        # 0 units

echo ""
if [[ -n "$COMMIT" ]]; then
    echo "Done. Check the storefront - the merged products should show a colour"
    echo "picker, and the old per-colour listings should be gone from the shop."
    echo "Remember to deactivate one of the two 'Gradient Finish' variants."
else
    echo "Dry run complete. Nothing changed."
fi
