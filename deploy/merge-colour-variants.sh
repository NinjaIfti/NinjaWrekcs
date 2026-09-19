#!/usr/bin/env bash
#
# Batch 3: the Lego sets, the RGB lamps, and a redo of Singularity 22cm.
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
# Already merged, do NOT re-run: RGX Butterfly 143, RGX Dagger 144,
# RGX Butterfly and Blade 145, Kuronami 7cm 146, Kuronami 22cm 147,
# Reaver Krambit 17cm 148, Reaver Vandal 149, CSGO Butterfly 150,
# CSGO Butterfly Trainer 151, Recon Butterfly 153.
#
# Ids are from the 2026-09-19 catalogue. Expected total stock carried in this
# batch: 10 units across 15 source products, becoming 3 products.

set -euo pipefail

cd "$(dirname "$0")/.."

COMMIT=""
if [[ "${1:-}" == "--commit" ]]; then
    COMMIT="--commit"
    echo "APPLYING. This one also UNDOES a previous merge - see the Singularity"
    echo "note below. Take a database backup first if you have not:"
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

# Singularity 22cm was merged in batch 2 from Yellow and Brown only. Blue (78)
# and Pink (79) are the same knife at the same 1,200, so product 152 is undone
# and rebuilt from all four. Nothing has been ordered from 152, so the undo
# deletes it rather than leaving a deactivated stray behind.
echo ""
echo "=============================================================="
echo " Undo batch 2's Singularity 22cm (product 152)"
echo "=============================================================="
php artisan products:unmerge-variants 152 --ids=122,123 $COMMIT

merge "Singularity 22cm" "122,123,78,79" "Yellow,Brown,Blue,Pink"   # 5 units

# Agent names rather than colours, so the variant names are the agents. The
# three already-deactivated sets (21 Reyna, 25 Raze, 31 KillJoy) are folded in
# as INACTIVE variants: hidden from the shop, but Raze's unit is preserved and
# any of them can be switched back on from admin. Active sets lead so they
# sort first.
merge "Valorant Agent Lego Set" \
      "23,24,28,29,30,21,25,31" \
      "Viper,Youru,Omen,Chamber,Sova,Reyna,Raze,KillJoy"            # 4 units

# Reyna is 1,200 against the other two at 1,000 - each variant keeps its own
# price, so the card will show a From-price.
merge "Valorant Agent RGB Lamp" "33,34,35" "Viper,Sova,Reyna"       # 1 unit

echo ""
if [[ -n "$COMMIT" ]]; then
    echo "Done. Check the storefront - the merged products should show a picker,"
    echo "and the old per-agent listings should be gone from the shop."
else
    echo "Dry run complete. Nothing changed."
fi
