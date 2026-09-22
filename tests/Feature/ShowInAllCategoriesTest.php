<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * "Show in all categories".
 *
 * A product with this ticked is listed under every category, not only its own.
 * It is one product row shown in many places - not copies - so its stock, its
 * variants, the cart and its orders are the same wherever it is found.
 */
class ShowInAllCategoriesTest extends TestCase
{
    use RefreshDatabase;

    private ?User $admin = null;

    protected function setUp(): void
    {
        parent::setUp();
        // The shop counts and the home page are both cached.
        Cache::flush();
    }

    private function admin(): User
    {
        return $this->admin ??= User::factory()->create(['email' => 'ifti3061@gmail.com']);
    }

    private function category(string $slug, string $name): Category
    {
        return Category::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'parent_id' => null, 'is_active' => true]
        );
    }

    private function product(string $name, Category $category, bool $everywhere): Product
    {
        return Product::factory()->create([
            'name' => $name,
            'category_id' => $category->id,
            'is_active' => true,
            'price' => 500,
            'quantity' => 4,
            'show_in_all_categories' => $everywhere,
        ]);
    }

    /** @return array<int, string> */
    private function namesListedUnder(Category $category): array
    {
        return $this->get(route('shop.index', ['category_id' => $category->id]))
            ->assertOk()
            ->original->getData()['products']
            ->pluck('name')
            ->all();
    }

    public function test_a_flagged_product_is_listed_under_a_category_that_is_not_its_own(): void
    {
        $csgo = $this->category('csgo', 'CS GO');
        $valorant = $this->category('valorant', 'Valorant');

        $this->product('Cleaning Kit', $csgo, everywhere: true);

        $this->assertContains('Cleaning Kit', $this->namesListedUnder($csgo));
        $this->assertContains('Cleaning Kit', $this->namesListedUnder($valorant));
    }

    public function test_an_unflagged_product_stays_in_its_own_category(): void
    {
        $csgo = $this->category('csgo', 'CS GO');
        $valorant = $this->category('valorant', 'Valorant');

        $this->product('Karambit', $csgo, everywhere: false);

        $this->assertContains('Karambit', $this->namesListedUnder($csgo));
        $this->assertNotContains('Karambit', $this->namesListedUnder($valorant));
    }

    /** Listed once, not twice, in the category it actually belongs to. */
    public function test_it_is_not_listed_twice_in_its_own_category(): void
    {
        $csgo = $this->category('csgo', 'CS GO');

        $this->product('Cleaning Kit', $csgo, everywhere: true);

        $names = $this->namesListedUnder($csgo);

        $this->assertSame(1, count(array_keys($names, 'Cleaning Kit')));
    }

    /**
     * The guarantee the feature rests on: every listing links to the same
     * product, so there is one stock figure and one cart line behind it.
     */
    public function test_every_category_links_to_the_same_product(): void
    {
        $csgo = $this->category('csgo', 'CS GO');
        $valorant = $this->category('valorant', 'Valorant');

        $product = $this->product('Cleaning Kit', $csgo, everywhere: true);

        $idUnderCsgo = $this->get(route('shop.index', ['category_id' => $csgo->id]))
            ->original->getData()['products']->firstWhere('name', 'Cleaning Kit')->id;
        $idUnderValorant = $this->get(route('shop.index', ['category_id' => $valorant->id]))
            ->original->getData()['products']->firstWhere('name', 'Cleaning Kit')->id;

        $this->assertSame($product->id, $idUnderCsgo);
        $this->assertSame($product->id, $idUnderValorant);
        $this->assertSame(1, Product::where('name', 'Cleaning Kit')->count(), 'No copies are made.');
    }

    /** The sidebar number has to match the cards underneath it. */
    public function test_the_category_count_includes_it(): void
    {
        $csgo = $this->category('csgo', 'CS GO');
        $valorant = $this->category('valorant', 'Valorant');

        $this->product('Cleaning Kit', $csgo, everywhere: true);
        $this->product('Vandal', $valorant, everywhere: false);

        $counts = $this->get(route('shop.index', ['category_id' => $valorant->id]))
            ->original->getData()['categoryCounts'];

        $this->assertSame(2, $counts[$valorant->id]);
        $this->assertSame(1, $counts[$csgo->id]);
    }

    public function test_the_home_page_category_showcase_includes_it(): void
    {
        $csgo = $this->category('csgo', 'CS GO');
        $valorant = $this->category('valorant', 'Valorant');

        $this->product('Cleaning Kit', $csgo, everywhere: true);
        $this->product('Vandal', $valorant, everywhere: false);

        $categories = $this->get('/')->assertOk()->original->getData()['categories'];

        $this->assertContains('Cleaning Kit', $categories->firstWhere('slug', 'valorant')->products->pluck('name')->all());
        $this->assertContains('Cleaning Kit', $categories->firstWhere('slug', 'csgo')->products->pluck('name')->all());
    }

    public function test_the_admin_form_saves_the_checkbox_and_can_clear_it(): void
    {
        $csgo = $this->category('csgo', 'CS GO');

        $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'name' => 'Cleaning Kit',
            'description' => 'x',
            'notes' => '',
            'category_id' => $csgo->id,
            'price' => 500,
            'quantity' => 4,
            'is_active' => '1',
            'show_in_all_categories' => '1',
        ]);

        $product = Product::where('name', 'Cleaning Kit')->firstOrFail();
        $this->assertTrue($product->show_in_all_categories);

        // An unticked checkbox is simply absent from the request.
        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'name' => 'Cleaning Kit',
            'description' => 'x',
            'notes' => '',
            'category_id' => $csgo->id,
            'price' => 500,
            'quantity' => 4,
            'is_active' => '1',
        ]);

        $this->assertFalse($product->fresh()->show_in_all_categories);
    }

    public function test_the_admin_form_shows_the_checkbox(): void
    {
        $product = $this->product('Cleaning Kit', $this->category('csgo', 'CS GO'), everywhere: true);

        $html = $this->actingAs($this->admin())->get(route('admin.products.edit', $product))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/name="show_in_all_categories"[^>]*checked/', $html);
    }
}
