# Category Setup Guide

This guide explains how to ensure the 4 main category cards are visible on your shop page.

## Problem
The main shop page should display 4 category cards:
1. Valorant
2. CS:GO
3. Toys
4. Pre-order/Upcoming

If these cards are not showing, it's likely because the categories are not marked as active in the database.

## Solution

### Option 1: Run the Seeder (Recommended)

The `CategorySeeder` has been updated to be idempotent (safe to run multiple times). It will:
- Create categories if they don't exist
- Update existing categories to ensure they're active
- Set proper order values

**Run on your production server:**
```bash
php artisan db:seed --class=CategorySeeder
```

Or run all seeders:
```bash
php artisan db:seed
```

### Option 2: Manual Database Check

If you prefer to check manually, run this SQL query on your production database:

```sql
SELECT id, name, slug, parent_id, is_active, `order` 
FROM categories 
WHERE parent_id IS NULL 
ORDER BY `order`;
```

Ensure all 4 categories exist and have:
- `is_active = 1` (or `true`)
- `parent_id = NULL`
- Proper `order` values (1, 2, 3, 4)

### Option 3: Update Categories Manually

If categories exist but are inactive, update them:

```sql
UPDATE categories 
SET is_active = 1 
WHERE slug IN ('valorant', 'csgo', 'toys', 'pre-order-upcoming') 
AND parent_id IS NULL;
```

## After Running the Seeder

1. **Clear cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

2. **Refresh your shop page** - The 4 category cards should now be visible.

## Expected Categories

The seeder creates these categories:

### Parent Categories:
- Valorant (slug: `valorant`, order: 1)
- CS:GO (slug: `csgo`, order: 2)
- Toys (slug: `toys`, order: 3)
- Pre-order/Upcoming (slug: `pre-order-upcoming`, order: 4)

### Valorant Subcategories:
- Weapons (slug: `valorant-weapons`)
- Melee (slug: `valorant-melee`)
- Bundles (slug: `valorant-bundles`)

### CS:GO Subcategories:
- Knife (slug: `csgo-knife`)

## Troubleshooting

If categories still don't show after running the seeder:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify database connection
3. Check if there are any errors in the browser console
4. Ensure the `categories` table exists and has the correct structure

## Notes

- The seeder uses `updateOrCreate()` so it's safe to run multiple times
- Categories will be created or updated to ensure they're active
- Existing products assigned to these categories will not be affected
