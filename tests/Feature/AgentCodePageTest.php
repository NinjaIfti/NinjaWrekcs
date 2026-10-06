<?php

namespace Tests\Feature;

use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The page a printed QR code opens.
 *
 * /agent-code used to hold the giveaway entry form and went when the giveaway
 * was removed, but the QR codes are already out there - the access log shows
 * scans landing on a 404. It now presents the AGENT10 coupon.
 *
 * The terms come from the coupon row rather than being written into the page,
 * so changing the discount in the admin panel changes what the page promises.
 * A promise on a printed card that the checkout then refuses is worse than no
 * page at all.
 */
class AgentCodePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function agent10(array $overrides = []): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'AGENT10',
            'type' => 'percentage',
            'value' => 10,
            'minimum_order' => 1400,
            'maximum_discount' => 200,
            'usage_limit' => null,
            'used_count' => 0,
            'is_active' => true,
        ], $overrides));
    }

    public function test_the_page_shows_the_code_and_its_terms(): void
    {
        $this->agent10();

        $response = $this->get('/agent-code');

        $response->assertOk();
        $response->assertSee('AGENT10');
        $response->assertSee('10%', false);
        $response->assertSee('1,400', false);
        $response->assertSee('200', false);
    }

    /** The whole point is that a scan no longer dead-ends on a 404. */
    public function test_the_page_exists_even_with_no_coupon_in_the_database(): void
    {
        $response = $this->get('/agent-code');

        $response->assertOk();
        $response->assertDontSee('AGENT10 is ready');
    }

    public function test_a_deactivated_coupon_is_not_offered_as_usable(): void
    {
        $this->agent10(['is_active' => false]);

        $response = $this->get('/agent-code');

        $response->assertOk();
        $response->assertSee('not available', false);
    }

    public function test_a_used_up_coupon_is_not_offered_as_usable(): void
    {
        $this->agent10(['usage_limit' => 5, 'used_count' => 5]);

        $response = $this->get('/agent-code');

        $response->assertOk();
        $response->assertSee('not available', false);
    }

    public function test_an_expired_coupon_is_not_offered_as_usable(): void
    {
        $this->agent10(['valid_until' => now()->subDay()]);

        $this->get('/agent-code')->assertOk()->assertSee('not available', false);
    }

    public function test_a_flat_amount_coupon_describes_itself_in_taka(): void
    {
        $this->agent10(['type' => 'fixed', 'value' => 150, 'maximum_discount' => null]);

        $this->get('/agent-code')->assertOk()->assertSee('৳150', false);
    }

    public function test_the_page_links_to_the_shop(): void
    {
        $this->agent10();

        $this->get('/agent-code')->assertOk()->assertSee(route('shop.index'), false);
    }
}
