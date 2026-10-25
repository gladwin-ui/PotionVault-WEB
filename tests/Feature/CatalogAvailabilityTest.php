<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Profile;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_catalog_count_matches_all_active_admin_units_while_rental_requires_available_status(): void
    {
        Unit::create($this->unit('CAT-AVAILABLE', 'Available Catalog Unit', 'AVAILABLE'));
        Unit::create($this->unit('CAT-RESERVED', 'Reserved Catalog Unit', 'RESERVED'));

        $customer = User::factory()->create(['role' => 'user']);
        Profile::create(['user_id' => $customer->id]);
        $response = $this->actingAs($customer)->get(route('catalog'));
        $response->assertOk()->assertViewHas('units', fn ($units) => $units->total() === 2)->assertSee('Available Catalog Unit')->assertSee('Reserved Catalog Unit')->assertSee('Tidak tersedia');
        $this->assertSame(2, Unit::count());
    }

    private function unit(string $code, string $name, string $status): array
    {
        $category = Category::firstOrCreate(['name' => 'Catalog Test']);
        return ['unit_code' => $code, 'name' => $name, 'description' => 'Catalog test unit', 'rental_price' => 10000, 'rarity' => 'Rare', 'condition' => 'GOOD', 'status' => $status, 'category_ids' => [$category->id]];
    }
}
