# Category Structure

## Overview
The shop has **4 main parent categories** (no subcategories except Valorant):

1. **Valorant** - Has 3 subcategories
2. **CS:GO** - No subcategories (products go directly here)
3. **Toys** - No subcategories (products go directly here)
4. **Pre-order/Upcoming** - No subcategories (products go directly here)

## Category Structure Details

### 1. Valorant (Parent Category)
- **Slug**: `valorant`
- **Has Subcategories**: Yes
- **Subcategories**:
  - **Knife** (`valorant-knife`)
  - **Agent Figures** (`valorant-agent-figures`)
  - **Keychains/Stickers** (`valorant-keychains-stickers`)

**Product Assignment**:
- Products can be assigned to Valorant parent OR to any of its 3 subcategories
- When viewing Valorant, shows products from parent + all 3 subcategories

### 2. CS:GO (Parent Category)
- **Slug**: `csgo`
- **Has Subcategories**: No
- **Product Assignment**: Products go directly to CS:GO parent category

### 3. Toys (Parent Category)
- **Slug**: `toys`
- **Has Subcategories**: No
- **Product Assignment**: Products go directly to Toys parent category

### 4. Pre-order/Upcoming (Parent Category)
- **Slug**: `pre-order-upcoming`
- **Has Subcategories**: No
- **Product Assignment**: Products go directly to Pre-order/Upcoming parent category

## Product Category Assignment Rules

1. **Valorant products** can be assigned to:
   - Valorant parent category (shows in all Valorant views)
   - OR to specific subcategory (Knife, Agent Figures, or Keychains/Stickers)

2. **CS:GO, Toys, Pre-order products** must be assigned to:
   - Their respective parent category only (no subcategories exist)

## Migration from Old System

If you have products with the old `category` enum field:
- `figures` → `valorant-agent-figures` (Valorant subcategory)
- `knives` → `csgo` (CS:GO parent)
- `stickers` → `valorant-keychains-stickers` (Valorant subcategory)

## Running the Seeder

To set up/update categories:
```bash
php artisan db:seed --class=CategorySeeder
```

This will:
- Create/update the 4 main categories
- Create/update only Valorant's 3 subcategories
- Remove any unwanted subcategories from other categories
- Ensure all categories are active

## Fixing Null Category IDs

If products have `null` category_id:

**Option 1: Run Migration**
```bash
php artisan migrate
```

**Option 2: Use Command**
```bash
php artisan products:fix-null-categories
```

**Option 3: Manual SQL**
```sql
-- Get category IDs first
SELECT id, name, slug FROM categories WHERE parent_id IS NULL;

-- Then update products based on old 'category' field
UPDATE products 
SET category_id = (SELECT id FROM categories WHERE slug = 'valorant-agent-figures' LIMIT 1)
WHERE category_id IS NULL AND category = 'figures' AND is_active = 1;

UPDATE products 
SET category_id = (SELECT id FROM categories WHERE slug = 'csgo' LIMIT 1)
WHERE category_id IS NULL AND category = 'knives' AND is_active = 1;

UPDATE products 
SET category_id = (SELECT id FROM categories WHERE slug = 'valorant-keychains-stickers' LIMIT 1)
WHERE category_id IS NULL AND category = 'stickers' AND is_active = 1;

-- Default remaining to Valorant parent
UPDATE products 
SET category_id = (SELECT id FROM categories WHERE slug = 'valorant' LIMIT 1)
WHERE category_id IS NULL AND is_active = 1;
```

## Important Notes

- **Only 4 parent categories** should exist
- **Only Valorant** should have subcategories
- Products assigned to CS:GO, Toys, or Pre-order/Upcoming go directly to the parent category
- The shop page shows 4 category cards (one for each parent category)
- When clicking Valorant, users can filter by its 3 subcategories
- When clicking CS:GO, Toys, or Pre-order, products show directly (no subcategory filters)
