<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The admin products list.
 *
 * Merging a family deactivates its sources rather than deleting them, so most
 * of the catalogue is merged-away colours - 76 of 121 products on production.
 * Listing them all buries the products actually for sale, and the stock column
 * read the legacy quantity column, which is 0 on every merged product.
 */
class AdminProductListTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['email' => 'ifti3061@gmail.com']);
    }

    public function test_inactive_products_are_hidden_by_default(): void
    {
        Product::factory()->withCategory()->create(['name' => 'CSGO Knife', 'is_active' => true]);
        Product::factory()->withCategory()->create(['name' => 'CSGO Shark Finish', 'is_active' => false]);

        $response = $this->actingAs($this->admin())->get(route('admin.products'));

        $response->assertOk();
        $response->assertSee('CSGO Knife');
        $response->assertDontSee('CSGO Shark Finish');
    }

    public function test_the_toggle_brings_inactive_products_back(): void
    {
        Product::factory()->withCategory()->create(['name' => 'CSGO Knife', 'is_active' => true]);
        Product::factory()->withCategory()->create(['name' => 'CSGO Shark Finish', 'is_active' => false]);

        $response = $this->actingAs($this->admin())->get(route('admin.products', ['show_inactive' => 1]));

        $response->assertOk();
        $response->assertSee('CSGO Knife');
        $response->assertSee('CSGO Shark Finish');
    }

    public function test_the_toggle_reports_how_many_are_hidden(): void
    {
        Product::factory()->withCategory()->create(['is_active' => true]);
        Product::factory()->count(3)->withCategory()->create(['is_active' => false]);

        $response = $this->actingAs($this->admin())->get(route('admin.products'));

        $response->assertOk();
        $response->assertSee('Include inactive (3)');
    }

    /**
     * The count on a tab has to agree with the rows under it, or the admin is
     * told there are products a filtered list will never show.
     */
    public function test_tab_counts_match_the_rows_shown(): void
    {
        Product::factory()->withCategory()->create(['is_active' => true]);
        Product::factory()->count(4)->withCategory()->create(['is_active' => false]);

        // One admin, reused - the email is unique and identifies the admin.
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.products'))
            ->assertOk()
            ->assertSee('1 products');

        $this->actingAs($admin)
            ->get(route('admin.products', ['show_inactive' => 1]))
            ->assertOk()
            ->assertSee('5 products');
    }

    public function test_the_stock_column_sums_the_variants(): void
    {
        // A merged product: no stock of its own, all of it on the variants.
        $product = Product::factory()->withCategory()->create([
            'name' => 'CSGO Knife',
            'quantity' => 0,
            'is_active' => true,
        ]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'quantity' => 4, 'is_active' => true]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'quantity' => 10, 'is_active' => true]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'quantity' => 99, 'is_active' => false]);

        $response = $this->actingAs($this->admin())->get(route('admin.products'));

        $response->assertOk();
        $response->assertSee('14');
        $response->assertSee('across 3 variants');
    }

    public function test_a_product_without_variants_shows_its_own_stock(): void
    {
        Product::factory()->withCategory()->create([
            'name' => 'CSGO HawkBill Krambit',
            'quantity' => 7,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.products'));

        $response->assertOk();
        $response->assertSee('7');
        $response->assertDontSee('across');
    }

    /**
     * The stock column sums variants and prints a category for every row, so
     * both availableStock() and the category_name accessor have to read the
     * eager-loaded relations. Querying either per row makes the list get slower
     * with every product added.
     *
     * The products share one category deliberately: the tab counts query per
     * category, which is bounded by how many categories exist, not by the size
     * of the catalogue. Giving each product its own category would measure that
     * instead of what this test is about.
     */
    public function test_listing_does_not_query_once_per_product(): void
    {
        $admin = $this->admin();
        $category = \App\Models\Category::factory()->create();

        $countQueriesFor = function (int $products) use ($admin, $category): int {
            Product::query()->delete();

            for ($i = 0; $i < $products; $i++) {
                $product = Product::factory()->create([
                    'category_id' => $category->id,
                    'is_active' => true,
                ]);
                ProductVariant::factory()->count(3)->create(['product_id' => $product->id]);
            }

            \DB::flushQueryLog();
            \DB::enableQueryLog();
            $this->actingAs($admin)->get(route('admin.products'))->assertOk();
            $count = count(\DB::getQueryLog());
            \DB::disableQueryLog();

            return $count;
        };

        $forTwo = $countQueriesFor(2);
        $forTen = $countQueriesFor(10);

        $this->assertSame(
            $forTwo,
            $forTen,
            "Listing 10 products ran {$forTen} queries against {$forTwo} for 2 - something is querying per row."
        );
    }

    public function test_the_toggle_keeps_the_selected_category(): void
    {
        $product = Product::factory()->withCategory()->create(['is_active' => false]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.products', ['category_id' => $product->category_id]));

        $response->assertOk();
        // The toggle link carries the tab through rather than dropping to "All".
        $response->assertSee('category_id=' . $product->category_id . '&amp;show_inactive=1', false);
    }
}
