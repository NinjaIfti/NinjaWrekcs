#!/usr/bin/env bash
#
# Batch 4: rebuild the CSGO knife product as "CSGO Knife" with the HawkBill
# Krambit included, and merge the boxed tier into its own product.
#
# Dry run by default - it prints every plan and changes nothing:
#
#     bash deploy/merge-colour-variants.sh
#
# Apply, once the plans read correctly:
#
#     bash deploy/merge-colour-variants.sh --commit
#
# This is a ONE-TIME catalogue operation, not a migration. To reverse a family:
#
#     php artisan products:unmerge-variants <merged-id> --ids=<source ids> --commit
#
# Already merged, do NOT re-run: RGX Butterfly 143, RGX Dagger 144,
# RGX Butterfly and Blade 145, Kuronami 7cm 146, Kuronami 22cm 147,
# Reaver Krambit 17cm 148, Reaver Vandal 149, CSGO Butterfly Trainer 151,
# Singularity 22cm, Recon Butterfly 153, Valorant Agent Lego Set,
# Valorant Agent RGB Lamp.
#
# Expected total stock carried in this batch: 23 units across 22 source
# products, becoming 2 products.

set -euo pipefail

cd "$(dirname "$0")/.."

COMMIT=""
if [[ "${1:-}" == "--commit" ]]; then
    COMMIT="--commit"
    echo "APPLYING. This one also UNDOES product 150 - see the note below."
    echo "Take a database backup first if you have not:"
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

# Product 150 was named "CSGO Butterfly", but most of its 950 variants are claw
# and hawkbill shapes rather than butterfly knives, so it is rebuilt under the
# neutral name "CSGO Knife" with the HawkBill Krambit (142) added as an 18th
# variant. Nothing has been ordered from 150, so the undo deletes it rather
# than leaving a deactivated stray behind.
echo ""
echo "=============================================================="
echo " Undo product 150 (CSGO Butterfly) so it can be rebuilt"
echo "=============================================================="
php artisan products:unmerge-variants 150 \
    --ids=101,102,103,104,135,81,82,83,84,85,86,87,88,89,90,91,92 $COMMIT

# NOTE: ids 81 and 88 are both "CSGO Gradient Finish", kept as asked, so two
# swatches read the same. Deactivate one variant in admin afterwards.
merge "CSGO Knife" \
      "101,102,103,104,135,81,82,83,84,85,86,87,88,89,90,91,92,142" \
      "Blue Shadow,Red Doppler,Red Shadow,Green Shadow,Gamma Doppler,Gradient Finish,Black Claw,White Chromium,Rainbow Chromium,Black Knife with Hole,Old School Finish,Celestial Finish,Gradient Finish,Simov Finish,Shark Finish,Sakura Finish,Blue Thunder Finish,HawkBill Krambit"

# The boxed tier is its own product: variants carry one axis, and these differ
# from the 950 versions by packaging rather than by finish. Celestial and Shark
# Finish therefore appear in both products, plain in one and boxed in the other.
merge "CSGO Butterfly Knife with Box & Cover" \
      "141,113,114,140" \
      "Celestial,Rainbow Fade,Shark Finish,Rainbow Fade (cover only)"

echo ""
if [[ -n "$COMMIT" ]]; then
    echo "Done. Check the storefront - CSGO Knife should carry 18 variants and"
    echo "14 units, and the boxed product 4 variants and 9 units."
    echo "Remember to deactivate one of the two 'Gradient Finish' variants."
else
    echo "Dry run complete. Nothing changed."
fi
