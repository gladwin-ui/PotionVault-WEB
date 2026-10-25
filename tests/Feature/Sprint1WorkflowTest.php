<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use App\Services\RentalWorkflowService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Sprint1WorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_constraints_cover_core_relationships(): void
    {
        $this->assertTrue(Schema::hasColumn('profiles', 'user_id'));
        $this->assertTrue(Schema::hasColumn('payments', 'refunded_at'));
        $this->assertTrue(Schema::hasColumn('payments', 'refunded_by'));
        $this->assertContains('rental_items_rental_unit_unique', array_column(Schema::getIndexes('rental_items'), 'name'));
    }

    public function test_core_customer_admin_workflow_reaches_return_and_fine(): void
    {
        [$customer, $admin] = $this->accounts();
        [$first, $second] = [Unit::create($this->unit('ONE-001', 25000)), Unit::create($this->unit('TWO-001', 35000))];
        $workflow = app(RentalWorkflowService::class);

        $rental = $workflow->request($customer->id, [$first->id, $second->id]);
        $this->assertSame('PENDING', $rental->status);
        $workflow->approve($rental, $admin->id);
        $workflow->confirmPayment($rental, $admin->id);
        $workflow->handover($rental);
        $rental->load('items');

        Carbon::setTestNow($rental->items->first()->due_at->copy()->addDay()->addSecond());
        $returned = $workflow->returnItem($rental->items->first(), 'GOOD');
        $this->assertSame(4000.0, (float) $returned->fine_amount);
        $this->assertSame('UNPAID', $returned->fine_payment_status);
        $this->assertSame('BORROWED', $rental->fresh()->status);
        $workflow->returnItem($rental->items->last(), 'GOOD');
        $this->assertSame('RETURNED', $rental->fresh()->status);
        Carbon::setTestNow();
    }

    public function test_expiry_releases_units_and_creates_auditable_refund_flow(): void
    {
        [$customer, $admin] = $this->accounts();
        $unit = Unit::create($this->unit('EXP-001', 40000));
        $workflow = app(RentalWorkflowService::class);
        $rental = $workflow->request($customer->id, [$unit->id]);
        $workflow->approve($rental, $admin->id);
        $workflow->confirmPayment($rental, $admin->id);
        $rental->update(['expires_at' => now()->subSecond()]);

        $this->assertSame(1, $workflow->expireApproved());
        $this->assertSame('EXPIRED', $rental->fresh()->status);
        $this->assertSame('REFUND_PENDING', $rental->fresh('payment')->payment->status);
        $this->assertSame('AVAILABLE', $unit->fresh()->status);
        $workflow->resolveRefund($rental, $admin->id, 'Dikembalikan secara manual');
        $this->assertSame('REFUNDED', $rental->fresh('payment')->payment->status);
        $this->assertNotNull($rental->fresh('payment')->payment->refunded_at);
    }

    public function test_late_handover_persists_expiry_and_refund_pending_before_rejecting(): void
    {
        [$customer, $admin] = $this->accounts();
        $unit = Unit::create($this->unit('HAND-001', 40000));
        $workflow = app(RentalWorkflowService::class);
        $rental = $workflow->request($customer->id, [$unit->id]);
        $workflow->approve($rental, $admin->id);
        $workflow->confirmPayment($rental, $admin->id);
        $rental->update(['expires_at' => now()->subSecond()]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        try {
            $workflow->handover($rental);
        } finally {
            $fresh = $rental->fresh(['payment', 'items.unit']);
            $this->assertSame('EXPIRED', $fresh->status);
            $this->assertSame('REFUND_PENDING', $fresh->payment->status);
            $this->assertSame('AVAILABLE', $fresh->items->first()->unit->status);
        }
    }

    public function test_late_handover_keeps_unpaid_payment_when_expiring(): void
    {
        [$customer, $admin] = $this->accounts();
        $unit = Unit::create($this->unit('HAND-002', 40000));
        $workflow = app(RentalWorkflowService::class);
        $rental = $workflow->request($customer->id, [$unit->id]);
        $workflow->approve($rental, $admin->id);
        $rental->update(['expires_at' => now()->subSecond()]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        try {
            $workflow->handover($rental);
        } finally {
            $fresh = $rental->fresh(['payment', 'items.unit']);
            $this->assertSame('EXPIRED', $fresh->status);
            $this->assertSame('UNPAID', $fresh->payment->status);
            $this->assertSame('AVAILABLE', $fresh->items->first()->unit->status);
        }
    }

    public function test_customer_cannot_exceed_two_active_units(): void
    {
        [$customer] = $this->accounts();
        $units = collect(range(1, 3))->map(fn ($number) => Unit::create($this->unit("CAP-00{$number}", 10000)));
        $workflow = app(RentalWorkflowService::class);
        $workflow->request($customer->id, [$units[0]->id, $units[1]->id]);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $workflow->request($customer->id, [$units[2]->id]);
    }

    public function test_admin_delete_is_a_soft_delete_and_keeps_history(): void
    {
        [, $admin] = $this->accounts();
        $unit = Unit::create($this->unit('DEL-001', 10000));
        $this->actingAs($admin)->delete(route('admin.artifacts.destroy', $unit))->assertRedirect();
        $this->assertSoftDeleted('units', ['id' => $unit->id]);
    }

    private function accounts(): array
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'user', 'is_active' => true]);
        Profile::create(['user_id' => $admin->id]);
        Profile::create(['user_id' => $customer->id]);
        return [$customer, $admin];
    }

    private function unit(string $code, int $price): array
    {
        return ['unit_code' => $code, 'name' => 'Test Artifact', 'description' => 'Artifact for automated testing', 'rental_price' => $price, 'rarity' => 'Rare', 'condition' => 'GOOD', 'status' => 'AVAILABLE'];
    }
}
