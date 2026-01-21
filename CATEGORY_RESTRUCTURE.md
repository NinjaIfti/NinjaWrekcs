# Category Restructure

## New Structure

```
Shop (Mother Category - Top Level)
├── Valorant
│   ├── Knives/Melees
│   ├── Agent Figures
│   └── Keychains & Stickers
├── CS:GO
├── Toys
└── Pre-order/Upcoming
```

## What Changed

1. **Added Mother Category**: "Shop" is now the top-level category
2. **4 Main Categories**: Under Shop, there are 4 categories:
   - Valorant (has 3 subcategories)
   - CS:GO (no subcategories)
   - Toys (no subcategories)
   - Pre-order/Upcoming (no subcategories)
3. **Valorant Subcategories**: 
   - Knives/Melees (combined from old "Knife" and "Melee")
   - Agent Figures
   - Keychains & Stickers

## Migration Process

The migration will:
1. ✅ Store all existing product category assignments
2. ✅ Store all old category information for mapping
3. ✅ Temporarily set all product category_id to null
4. ✅ Delete all old categories
5. ✅ Create new category structure
6. ✅ Migrate products to new categories based on old category slugs

## Running the Migration

**⚠️ IMPORTANT: Backup your database first!**

```bash
# Run the migration
php artisan migrate

# Or run specific migration
php artisan migrate --path=database/migrations/2026_01_21_100000_restructure_categories.php
```

## After Migration

1. **Clear cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

2. **Verify categories:**
   ```bash
   php artisan categories:count
   ```

3. **Check product assignments:**
   ```bash
   php artisan products:check-category-ids
   ```

## Category Mapping

### Old → New Parent Categories:
- `valorant` → `valorant` (under Shop)
- `csgo` → `csgo` (under Shop)
- `toys` → `toys` (under Shop)
- `pre-order-upcoming` → `pre-order-upcoming` (under Shop)

### Old → New Subcategories:
- `valorant-knife` → `valorant-knives-melees`
- `valorant-melee` → `valorant-knives-melees`
- `valorant-weapons` → `valorant-knives-melees`
- `valorant-agent-figures` → `valorant-agent-figures` (same)
- `valorant-keychains-stickers` → `valorant-keychains-stickers` (same)
- `valorant-bundles` → `valorant-keychains-stickers`
- `csgo-knife` → `csgo` (parent category, no subcategories)

## Expected Result

After migration, you should have:
- **1 Mother Category**: Shop
- **4 Main Categories**: Valorant, CS:GO, Toys, Pre-order/Upcoming
- **3 Valorant Subcategories**: Knives/Melees, Agent Figures, Keychains & Stickers
- **Total: 8 categories** (1 mother + 4 main + 3 subcategories)

All existing products will be migrated to the new structure automatically.

## Rollback

⚠️ **This migration is destructive and cannot be easily rolled back.**

If you need to rollback, you would need to:
1. Restore from database backup
2. Or manually recreate old categories and reassign products

## Notes

- The shop page will show the 4 main categories (Valorant, CS:GO, Toys, Pre-order/Upcoming)
- The mother category "Shop" is not displayed on the frontend
- Products assigned to old subcategories will be mapped to new subcategories
- Products assigned to old parent categories will be mapped to new parent categories
- If a product's old category doesn't map to any new category, it will be assigned to Valorant by default
