<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Profile;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use App\Services\RentalWorkflowService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Sprint2ComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_change_password_only_with_current_password_and_confirmation(): void
    {
        [$customer] = $this->accounts();
        $this->actingAs($customer)->put(route('profile.password.update'), [
            'current_password' => 'wrong-password', 'password' => 'new-password', 'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('password', $customer->fresh()->password));

        $this->actingAs($customer)->put(route('profile.password.update'), [
            'current_password' => 'password', 'password' => 'new-password', 'password_confirmation' => 'different-password',
        ])->assertSessionHasErrors('password');

        $this->actingAs($customer)->put(route('profile.password.update'), [
            'current_password' => 'password', 'password' => 'new-password', 'password_confirmation' => 'new-password',
        ])->assertSessionHas('password_status');
        $this->assertTrue(Hash::check('new-password', $customer->fresh()->password));
    }

    public function test_admin_password_and_profile_email_updates_are_scoped_to_authenticated_account(): void
    {
        [$customer, $admin] = $this->accounts();
        $this->actingAs($admin)->put(route('admin.profile.update'), ['name' => 'Keeper', 'email' => 'keeper@example.test'])->assertSessionHas('status');
        $this->assertSame('keeper@example.test', $admin->fresh()->email);
        $this->actingAs($admin)->put(route('admin.profile.password.update'), ['current_password' => 'password', 'password' => 'admin-new-password', 'password_confirmation' => 'admin-new-password'])->assertSessionHas('password_status');
        $this->assertTrue(Hash::check('admin-new-password', $admin->fresh()->password));

        $this->actingAs($customer)->put(route('profile.update'), ['name' => 'Customer', 'email' => 'customer-new@example.test', 'phone' => '0812'])->assertSessionHas('status');
        $this->assertSame('customer-new@example.test', $customer->fresh()->email);
        $this->assertNotSame($admin->id, $customer->fresh()->id);
    }

    public function test_customer_dashboard_and_rental_detail_expose_due_dates_and_unit_details(): void
    {
        [$customer, $admin] = $this->accounts();
        $unit = Unit::create($this->unit('DASH-001', 25000));
        $workflow = app(RentalWorkflowService::class);
        $rental = $workflow->request($customer->id, [$unit->id]);
        $workflow->approve($rental, $admin->id);
        $workflow->confirmPayment($rental, $admin->id);
        $workflow->handover($rental);
        $rental = $rental->fresh(['items', 'payment']);
        $dueAt = $rental->items->first()->due_at;

        $this->actingAs($customer)->get(route('dashboard'))->assertOk()->assertViewHas('stats', fn ($stats) => $stats['active'] === 1 && $stats['nearest_due_at']->equalTo($dueAt));
        $this->actingAs($customer)->get(route('rentals.show', $rental))->assertOk()->assertSee($dueAt->format('d/m/Y H:i'))->assertSee('BORROWED');
    }

    public function test_dashboard_is_database_driven_and_excludes_refunded_revenue(): void
    {
        [, $admin] = $this->accounts();
        Unit::create($this->unit('COM-001', 10000, 'Common'));
        Unit::create($this->unit('LEG-001', 50000, 'Legendary'));
        $rental = Rental::create(['user_id' => $admin->id, 'status' => 'EXPIRED', 'rental_total' => 50000]);
        Payment::create(['rental_id' => $rental->id, 'amount' => 50000, 'status' => 'REFUNDED', 'paid_at' => now()]);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertViewHas('rarityValues', fn ($values) => $values->sum() === 2)->assertViewHas('stats', fn ($stats) => (float) $stats['revenue'] === 0.0);
    }

    public function test_report_filters_artifact_and_fine_status_and_exposes_financial_totals(): void
    {
        [$customer, $admin] = $this->accounts();
        $unit = Unit::create($this->unit('REP-001', 25000));
        $rental = Rental::create(['user_id' => $customer->id, 'status' => 'RETURNED', 'rental_total' => 25000]);
        $rental->items()->create(['unit_id' => $unit->id, 'rental_price_snapshot' => 25000, 'fine_amount' => 2000, 'fine_payment_status' => 'UNPAID', 'returned_at' => now()]);
        $rental->payment()->create(['amount' => 25000, 'status' => 'PAID', 'paid_at' => now()]);
        $this->actingAs($admin)->get(route('admin.reports', ['artifact' => 'REP-001', 'fine_status' => 'UNPAID']))->assertOk()->assertViewHas('rentals', fn ($rentals) => $rentals->contains('id', $rental->id))->assertViewHas('totals', fn ($totals) => (float) $totals['confirmed'] === 25000.0 && (float) $totals['unpaid_fines'] === 2000.0);
    }

    public function test_cancelled_request_releases_unit_and_does_not_consume_quota(): void
    {
        [$customer] = $this->accounts();
        $first = Unit::create($this->unit('CAN-001', 10000));
        $second = Unit::create($this->unit('CAN-002', 10000));
        $workflow = app(RentalWorkflowService::class);
        $rental = $workflow->request($customer->id, [$first->id]);
        $workflow->cancelByCustomer($rental, $customer->id);
        $this->assertSame('CANCELLED', $rental->fresh()->status);
        $this->assertSame('AVAILABLE', $first->fresh()->status);
        $this->actingAs($customer)->get(route('rentals.show', $rental))->assertOk()->assertSee('<span class="badge cancelled">CANCELLED</span>', false);
        $next = $workflow->request($customer->id, [$second->id]);
        $this->assertSame('PENDING', $next->status);
    }

    public function test_fine_payment_is_independent_from_rental_payment(): void
    {
        [$customer, $admin] = $this->accounts();
        $unit = Unit::create($this->unit('FINE-001', 20000));
        $workflow = app(RentalWorkflowService::class);
        $rental = $workflow->request($customer->id, [$unit->id]);
        $workflow->approve($rental, $admin->id);
        $workflow->confirmPayment($rental, $admin->id);
        $workflow->handover($rental);
        $item = $rental->fresh('items')->items->first();
        Carbon::setTestNow($item->due_at->copy()->addDay());
        $item = $workflow->returnItem($item, 'GOOD');
        $workflow->payFine($item, $admin->id);
        Carbon::setTestNow();
        $this->assertSame('PAID', $rental->fresh('payment')->payment->status);
        $this->assertSame('PAID', $item->fresh()->fine_payment_status);
    }

    public function test_admin_can_soft_delete_and_restore_customer_without_losing_profile(): void
    {
        [$customer, $admin] = $this->accounts();
        $this->actingAs($admin)->delete(route('admin.customers.destroy', $customer))->assertRedirect();
        $this->assertSoftDeleted('users', ['id' => $customer->id]);
        $this->assertSame($customer->id, $customer->fresh('profile')->profile->user_id);
        $this->actingAs($admin)->post(route('admin.customers.restore', $customer->id))->assertRedirect();
        $this->assertTrue($customer->fresh()->is_active);
        $this->assertFalse($customer->fresh()->trashed());
    }

    private function accounts(): array
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'user', 'is_active' => true]);
        Profile::create(['user_id' => $admin->id]);
        Profile::create(['user_id' => $customer->id]);
        return [$customer, $admin];
    }

    private function unit(string $code, int $price, string $rarity = 'Rare'): array
    {
        return ['unit_code' => $code, 'name' => 'Report Artifact', 'description' => 'Artifact for testing', 'rental_price' => $price, 'rarity' => $rarity, 'condition' => 'GOOD', 'status' => 'AVAILABLE'];
    }
}
