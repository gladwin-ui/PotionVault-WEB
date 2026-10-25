<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Profile;
use App\Models\Unit;
use App\Models\User;
use App\Services\RentalWorkflowService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PartialRequirementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_names_are_allowed_but_duplicate_unit_codes_are_rejected(): void
    {
        [$customer, $admin] = $this->accounts();
        $first = Unit::create($this->unit('DUP-001', 'Same Name'));
        $second = Unit::create($this->unit('DUP-002', 'Same Name'));
        $this->assertNotSame($first->id, $second->id);

        $this->actingAs($admin)->from(route('admin.artifacts.create'))->post(route('admin.artifacts.store'), $this->unit('DUP-001', 'Third Name') + ['category_ids' => [$this->category()->id]])
            ->assertSessionHasErrors('unit_code');
        $this->assertDatabaseCount('units', 2);
    }

    public function test_multiple_categories_persist_on_artifact(): void
    {
        [, $admin] = $this->accounts();
        $categories = [Category::create(['name' => 'Arcane']), Category::create(['name' => 'Rare Finds'])];
        $unit = Unit::create($this->unit('CAT-001', 'Multi Category'));
        $unit->categories()->sync(collect($categories)->pluck('id'));
        $this->assertSame($categories[0]->id . ',' . $categories[1]->id, $unit->fresh('categories')->categories->pluck('id')->sort()->implode(','));

        $this->actingAs($admin)->get(route('admin.artifacts.edit', $unit))->assertOk()->assertSee('Arcane')->assertSee('Rare Finds');
    }

    public function test_customer_rental_isolation_and_admin_only_return_are_enforced(): void
    {
        [$customer, $admin, $other] = $this->accounts(true);
        $unit = Unit::create($this->unit('ISO-001', 'Isolated Artifact'));
        $workflow = app(RentalWorkflowService::class);
        $rental = $workflow->request($customer->id, [$unit->id]);
        $workflow->approve($rental, $admin->id);
        $workflow->confirmPayment($rental, $admin->id);
        $workflow->handover($rental);
        $item = $rental->fresh('items')->items->first();

        $this->actingAs($other)->get(route('rentals.show', $rental))->assertNotFound();
        $this->actingAs($other)->post(route('admin.returns.confirm', $item), ['condition' => 'GOOD'])->assertForbidden();
        $this->assertNull($item->fresh()->returned_at);
        $this->actingAs($customer)->get(route('rentals.show', $rental))->assertOk();
        $this->actingAs($admin)->post(route('admin.returns.confirm', $item), ['condition' => 'GOOD'])->assertRedirect();
        $this->assertNotNull($item->fresh()->returned_at);
    }

    public function test_handover_due_date_is_exactly_five_days_and_fine_is_calculated(): void
    {
        [$customer, $admin] = $this->accounts();
        $unit = Unit::create($this->unit('DUE-001', 'Due Date Artifact'));
        $workflow = app(RentalWorkflowService::class);
        Carbon::setTestNow(Carbon::parse('2026-10-25 10:00:00'));
        $rental = $workflow->request($customer->id, [$unit->id]);
        $workflow->approve($rental, $admin->id);
        $workflow->confirmPayment($rental, $admin->id);
        $workflow->handover($rental);
        $item = $rental->fresh('items')->items->first();
        $this->assertTrue($item->due_at->equalTo(Carbon::parse('2026-10-30 10:00:00')));
        Carbon::setTestNow($item->due_at->copy()->addHours(25));
        $returned = $workflow->returnItem($item, 'GOOD');
        $this->assertSame(4000.0, (float) $returned->fine_amount);
        Carbon::setTestNow();
    }

    public function test_invalid_server_requests_do_not_persist(): void
    {
        [$customer, $admin] = $this->accounts();
        $this->from(route('register'))->post(route('register.store'), ['name' => '', 'email' => 'bad', 'phone' => '', 'password' => 'x', 'password_confirmation' => 'y'])->assertSessionHasErrors(['name', 'email', 'phone', 'password']);
        $this->actingAs($admin)->post(route('admin.categories.store'), ['name' => ''])->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('categories', ['name' => '']);
        $this->actingAs($customer)->post(route('rentals.store'), ['unit_ids' => []])->assertSessionHasErrors('unit_ids');
    }

    private function accounts(bool $withOther = false): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user']);
        Profile::create(['user_id' => $admin->id]);
        Profile::create(['user_id' => $customer->id]);
        if (!$withOther) return [$customer, $admin];
        $other = User::factory()->create(['role' => 'user']);
        Profile::create(['user_id' => $other->id]);
        return [$customer, $admin, $other];
    }

    private function category(): Category
    {
        return Category::create(['name' => 'Test Category']);
    }

    private function unit(string $code, string $name): array
    {
        return ['unit_code' => $code, 'name' => $name, 'description' => 'Test artifact', 'rental_price' => 10000, 'rarity' => 'Rare', 'condition' => 'GOOD', 'status' => 'AVAILABLE'];
    }
}
