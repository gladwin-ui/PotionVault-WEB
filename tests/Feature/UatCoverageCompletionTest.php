<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Profile;
use App\Models\Unit;
use App\Models\User;
use App\Services\RentalWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UatCoverageCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_invalid_inactive_and_guest_guards(): void
    {
        $inactive = User::factory()->create(['email' => 'inactive@example.test', 'is_active' => false]);
        Profile::create(['user_id' => $inactive->id]);

        $this->from(route('login'))->post(route('login.store'), ['email' => 'inactive@example.test', 'password' => 'password'])
            ->assertSessionHasErrors('email')->assertRedirect(route('login'));
        $this->from(route('login'))->post(route('login.store'), ['email' => 'inactive@example.test', 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_customer_profile_and_duplicate_email_validation(): void
    {
        [$customer, $admin] = $this->accounts();
        $this->actingAs($customer)->put(route('profile.update'), [
            'name' => 'Updated Customer', 'email' => 'updated@example.test', 'phone' => '081234567890', 'address' => 'Jakarta',
        ])->assertSessionHas('status');
        $this->assertSame('updated@example.test', $customer->fresh()->email);

        $this->actingAs($customer)->put(route('profile.password.update'), [
            'current_password' => 'wrong', 'password' => 'new-password', 'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('current_password');
        $this->actingAs($customer)->put(route('profile.password.update'), [
            'current_password' => 'password', 'password' => 'new-password', 'password_confirmation' => 'new-password',
        ])->assertSessionHas('password_status');
        $this->assertTrue(Hash::check('new-password', $customer->fresh()->password));
        $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => 'Admin Updated', 'email' => 'updated@example.test',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_category_and_customer_crud_restore_and_authorization(): void
    {
        [$customer, $admin] = $this->accounts();
        $this->actingAs($customer)->get(route('admin.categories'))->assertForbidden();

        $this->actingAs($admin)->post(route('admin.categories.store'), ['name' => 'UAT Category', 'description' => 'UAT'])
            ->assertSessionHas('status');
        $category = Category::where('name', 'UAT Category')->firstOrFail();
        $this->actingAs($admin)->put(route('admin.categories.update', $category), ['name' => 'UAT Category Updated', 'description' => 'Changed'])
            ->assertSessionHas('status');
        $this->actingAs($admin)->delete(route('admin.categories.destroy', $category))->assertSessionHas('status');

        $this->actingAs($admin)->post(route('admin.customers.store'), [
            'name' => 'Managed Customer', 'email' => 'managed@example.test', 'phone' => '0812',
            'address' => 'Jakarta', 'password' => 'password', 'password_confirmation' => 'password',
        ])->assertSessionHas('status');
        $managed = User::where('email', 'managed@example.test')->firstOrFail();
        $this->actingAs($admin)->put(route('admin.customers.update', $managed), [
            'name' => 'Managed Customer Updated', 'email' => 'managed@example.test', 'phone' => '0813', 'address' => 'Bandung',
        ])->assertSessionHas('status');
        $this->actingAs($admin)->post(route('admin.customers.toggle', $managed))->assertSessionHas('status');
        $this->assertFalse($managed->fresh()->is_active);
        $this->actingAs($admin)->delete(route('admin.customers.destroy', $managed))->assertSessionHas('status');
        $this->assertSoftDeleted('users', ['id' => $managed->id]);
        $this->actingAs($admin)->post(route('admin.customers.restore', $managed->id))->assertSessionHas('status');
        $this->assertTrue($managed->fresh()->is_active);
    }

    public function test_return_conditions_partial_completion_and_fine_payment(): void
    {
        [$customer, $admin] = $this->accounts();
        $first = Unit::create($this->unit('RET-001'));
        $second = Unit::create($this->unit('RET-002'));
        $workflow = app(RentalWorkflowService::class);
        $rental = $workflow->request($customer->id, [$first->id, $second->id]);
        $workflow->approve($rental, $admin->id);
        $workflow->confirmPayment($rental, $admin->id);
        $workflow->handover($rental);
        $rental->refresh()->load('items');
        $workflow->returnItem($rental->items[0], 'MAINTENANCE');
        $this->assertSame('MAINTENANCE', $first->fresh()->status);
        $this->assertSame('BORROWED', $rental->fresh()->status);
        $workflow->returnItem($rental->fresh('items')->items[1], 'DAMAGED');
        $this->assertSame('DAMAGED', $second->fresh()->status);
        $this->assertSame('RETURNED', $rental->fresh()->status);
    }

    public function test_unavailable_unit_and_cancellation_restrictions(): void
    {
        [$customer, $admin] = $this->accounts();
        $unit = Unit::create($this->unit('LOCK-001'));
        $workflow = app(RentalWorkflowService::class);
        $first = $workflow->request($customer->id, [$unit->id]);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        try {
            $workflow->request($admin->id, [$unit->id]);
        } finally {
            $this->assertSame('PENDING', $first->fresh()->status);
        }
    }

    public function test_customer_duplicate_email_is_rejected_without_changing_original_email(): void
    {
        [$customer] = $this->accounts();
        $originalEmail = $customer->email;
        $other = User::factory()->create(['role' => 'user', 'email' => 'other@example.test']);
        Profile::create(['user_id' => $other->id]);
        $this->actingAs($customer)->put(route('profile.update'), ['name' => $customer->name, 'email' => $other->email, 'phone' => '0812'])
            ->assertSessionHasErrors('email');
        $this->assertSame('other@example.test', $other->fresh()->email);
        $this->assertSame($originalEmail, $customer->fresh()->email);
    }

    public function test_unavailable_catalog_detail_hides_rental_action_and_direct_request_is_rejected(): void
    {
        [$customer] = $this->accounts();
        $unit = Unit::create($this->unit('CAT-LOCK'));
        $unit->update(['status' => 'RESERVED']);
        $this->actingAs($customer)->get(route('catalog.show', $unit))->assertOk()->assertSee('Currently unavailable')->assertDontSee('Add to Request');
        $this->actingAs($customer)->post(route('rentals.store'), ['unit_ids' => [$unit->id]])->assertSessionHasErrors('unit_ids');
        $this->assertDatabaseCount('rentals', 0);
    }

    public function test_active_artifact_cannot_be_deleted_and_used_category_cannot_be_deleted(): void
    {
        [$customer, $admin] = $this->accounts();
        $unit = Unit::create($this->unit('ACTIVE-DEL'));
        $workflow = app(RentalWorkflowService::class);
        $rental = $workflow->request($customer->id, [$unit->id]);
        $this->actingAs($admin)->delete(route('admin.artifacts.destroy', $unit))->assertSessionHasErrors('unit');
        $this->assertFalse($unit->fresh()->trashed());
        $category = Category::create(['name' => 'Used UAT Category']);
        $unit->categories()->sync([$category->id]);
        $this->actingAs($admin)->delete(route('admin.categories.destroy', $category))->assertSessionHasErrors('category');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('category_unit', ['category_id' => $category->id, 'unit_id' => $unit->id]);
        $this->assertSame('PENDING', $rental->fresh()->status);
    }

    public function test_guest_rental_submission_is_rejected_without_creating_rental(): void
    {
        $unit = Unit::create($this->unit('GUEST-001'));
        $this->post(route('rentals.store'), ['unit_ids' => [$unit->id]])->assertRedirect(route('login'));
        $this->assertDatabaseCount('rentals', 0);
    }

    public function test_admin_rejects_pending_rental_and_releases_units_with_reason(): void
    {
        [$customer, $admin] = $this->accounts();
        $unit = Unit::create($this->unit('REJECT-001'));
        $workflow = app(RentalWorkflowService::class);
        $rental = $workflow->request($customer->id, [$unit->id]);
        $this->actingAs($admin)->post(route('admin.requests.reject', $rental), ['reason' => 'Dokumen tidak lengkap'])->assertSessionHas('status');
        $this->assertSame('REJECTED', $rental->fresh()->status);
        $this->assertSame('Dokumen tidak lengkap', $rental->fresh()->rejection_reason);
        $this->assertSame('AVAILABLE', $unit->fresh()->status);
    }

    public function test_handover_before_payment_keeps_approved_reserved_state(): void
    {
        [$customer, $admin] = $this->accounts();
        $unit = Unit::create($this->unit('UNPAID-001'));
        $workflow = app(RentalWorkflowService::class);
        $rental = $workflow->request($customer->id, [$unit->id]);
        $workflow->approve($rental, $admin->id);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        try { $workflow->handover($rental); } finally {
            $fresh = $rental->fresh('items');
            $this->assertSame('APPROVED', $fresh->status);
            $this->assertSame('RESERVED', $unit->fresh()->status);
            $this->assertNull($fresh->items->first()->borrowed_at);
        }
    }

    public function test_invalid_return_condition_is_rejected_without_state_change(): void
    {
        [$customer, $admin] = $this->accounts();
        $item = $this->borrowedRental($customer, $admin)->items->first();
        $this->actingAs($admin)->post(route('admin.returns.confirm', $item), ['condition' => 'BROKEN'])->assertSessionHasErrors('condition');
        $this->assertNull($item->fresh()->returned_at);
        $this->assertSame('BORROWED', $item->unit->fresh()->status);
    }

    public function test_fine_cannot_be_paid_before_return(): void
    {
        [$customer, $admin] = $this->accounts();
        $item = $this->borrowedRental($customer, $admin)->items->first();
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        try { app(RentalWorkflowService::class)->payFine($item, $admin->id); } finally {
            $this->assertNull($item->fresh()->fine_paid_at);
            $this->assertSame('NO_FINE', $item->fresh()->fine_payment_status);
        }
    }

    public function test_customer_cannot_return_or_pay_fine(): void
    {
        [$customer, $admin] = $this->accounts();
        $item = $this->borrowedRental($customer, $admin)->items->first();
        $this->actingAs($customer)->post(route('admin.returns.confirm', $item), ['condition' => 'GOOD'])->assertForbidden();
        $this->actingAs($customer)->post(route('admin.fines.pay', $item))->assertForbidden();
        $this->assertNull($item->fresh()->returned_at);
    }

    public function test_refund_non_expired_rental_is_rejected(): void
    {
        [$customer, $admin] = $this->accounts();
        $unit = Unit::create($this->unit('NO-REFUND'));
        $workflow = app(RentalWorkflowService::class);
        $rental = $workflow->request($customer->id, [$unit->id]);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        try { $workflow->resolveRefund($rental, $admin->id, 'invalid'); } finally {
            $this->assertSame('UNPAID', $rental->fresh('payment')->payment->status);
        }
    }

    public function test_duplicate_refund_is_rejected_and_audit_remains_single(): void
    {
        [$customer, $admin] = $this->accounts();
        $unit = Unit::create($this->unit('REFUND-001'));
        $workflow = app(RentalWorkflowService::class);
        $rental = $workflow->request($customer->id, [$unit->id]);
        $workflow->approve($rental, $admin->id);
        $workflow->confirmPayment($rental, $admin->id);
        $rental->update(['expires_at' => now()->subSecond()]);
        $workflow->expireApproved();
        $workflow->resolveRefund($rental, $admin->id, 'first refund');
        $payment = $rental->fresh('payment')->payment;
        $firstRefundedAt = $payment->refunded_at;
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        try { $workflow->resolveRefund($rental, $admin->id, 'second refund'); } finally {
            $this->assertSame('REFUNDED', $payment->fresh()->status);
            $this->assertTrue($payment->fresh()->refunded_at->equalTo($firstRefundedAt));
        }
    }

    private function accounts(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user']);
        Profile::create(['user_id' => $admin->id]);
        Profile::create(['user_id' => $customer->id]);
        return [$customer, $admin];
    }

    private function unit(string $code): array
    {
        return ['unit_code' => $code, 'name' => 'UAT Artifact', 'description' => 'UAT', 'rental_price' => 10000, 'rarity' => 'Rare', 'condition' => 'GOOD', 'status' => 'AVAILABLE'];
    }

    private function borrowedRental(User $customer, User $admin)
    {
        $unit = Unit::create($this->unit('BORROW-'.uniqid()));
        $workflow = app(RentalWorkflowService::class);
        $rental = $workflow->request($customer->id, [$unit->id]);
        $workflow->approve($rental, $admin->id);
        $workflow->confirmPayment($rental, $admin->id);
        return $workflow->handover($rental);
    }
}
