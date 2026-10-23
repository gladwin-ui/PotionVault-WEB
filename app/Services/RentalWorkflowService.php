<?php

namespace App\Services;

use App\Models\Rental;
use App\Models\RentalItem;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RentalWorkflowService
{
    private const ACTIVE_RENTAL_STATUSES = ['PENDING', 'APPROVED', 'BORROWED'];
    private const LATE_RATE = 2000;

    public function request(int $userId, array $unitIds): Rental
    {
        return DB::transaction(function () use ($userId, $unitIds) {
            $user = \App\Models\User::findOrFail($userId);
            if (!$user->is_active || $user->role !== 'user') throw ValidationException::withMessages(['account' => 'Akun customer tidak aktif.']);
            $unitIds = array_values(array_unique(array_map('intval', $unitIds)));
            if (count($unitIds) < 1 || count($unitIds) > 2) throw ValidationException::withMessages(['unit_ids' => 'Pilih satu atau dua unit.']);
            $active = RentalItem::whereHas('rental', fn ($q) => $q->where('user_id', $userId)->whereIn('status', self::ACTIVE_RENTAL_STATUSES))->whereNull('returned_at')->count();
            if ($active + count($unitIds) > 2) throw ValidationException::withMessages(['unit_ids' => 'Maksimal dua unit aktif diperbolehkan.']);
            $units = Unit::whereIn('id', $unitIds)->lockForUpdate()->get()->keyBy('id');
            if ($units->count() !== count($unitIds) || $units->contains(fn (Unit $unit) => $unit->status !== 'AVAILABLE')) throw ValidationException::withMessages(['unit_ids' => 'Salah satu unit sudah tidak tersedia.']);
            $total = $units->sum('rental_price');
            $rental = Rental::create(['user_id' => $userId, 'status' => 'PENDING', 'rental_total' => $total]);
            foreach ($units as $unit) { $rental->items()->create(['unit_id' => $unit->id, 'rental_price_snapshot' => $unit->rental_price]); $unit->update(['status' => 'RESERVED']); }
            $rental->payment()->create(['amount' => $total, 'status' => 'UNPAID']);
            return $rental->load('items.unit', 'payment');
        });
    }

    public function approve(Rental $rental, int $adminId): Rental
    {
        return DB::transaction(function () use ($rental, $adminId) { $rental = Rental::lockForUpdate()->findOrFail($rental->id); if ($rental->status !== 'PENDING') throw ValidationException::withMessages(['rental' => 'Rental bukan PENDING.']); $now = now(); $rental->update(['status' => 'APPROVED', 'approved_at' => $now, 'approved_by' => $adminId, 'expires_at' => $now->copy()->addDay()]); return $rental->fresh(); });
    }

    public function reject(Rental $rental, int $adminId, ?string $reason = null): Rental
    {
        return DB::transaction(function () use ($rental, $reason) { $rental = Rental::lockForUpdate()->findOrFail($rental->id); if ($rental->status !== 'PENDING') throw ValidationException::withMessages(['rental' => 'Rental bukan PENDING.']); $rental->update(['status' => 'REJECTED', 'rejection_reason' => $reason]); $this->releaseUnits($rental); return $rental->fresh(); });
    }

    public function cancelByCustomer(Rental $rental, int $userId): Rental
    {
        return DB::transaction(function () use ($rental, $userId) {
            $rental = Rental::with('items.unit')->lockForUpdate()->findOrFail($rental->id);
            if ((int) $rental->user_id !== $userId) throw ValidationException::withMessages(['rental' => 'Rental tidak ditemukan.']);
            if ($rental->status !== 'PENDING') throw ValidationException::withMessages(['rental' => 'Rental hanya dapat dibatalkan saat masih menunggu approval.']);
            $rental->update(['status' => 'CANCELLED']);
            $this->releaseUnits($rental);
            return $rental->fresh('items.unit', 'payment');
        });
    }

    public function expireApproved(): int
    {
        return Rental::where('status', 'APPROVED')->where('expires_at', '<=', now())->get()->each(function (Rental $rental) { DB::transaction(function () use ($rental) { $rental->update(['status' => 'EXPIRED']); $this->releaseUnits($rental); }); })->count();
    }

    public function confirmPayment(Rental $rental, int $adminId): Rental
    {
        return DB::transaction(function () use ($rental, $adminId) { $rental = Rental::lockForUpdate()->findOrFail($rental->id); $this->assertApprovedAndValid($rental); $payment = $rental->payment()->lockForUpdate()->firstOrFail(); if ((float) $payment->amount !== (float) $rental->rental_total) throw ValidationException::withMessages(['payment' => 'Nominal pembayaran tidak sesuai.']); $payment->update(['status' => 'PAID', 'paid_at' => now(), 'confirmed_by' => $adminId]); return $rental->fresh('payment'); });
    }

    public function handover(Rental $rental): Rental
    {
        return DB::transaction(function () use ($rental) { $rental = Rental::with('items.unit', 'payment')->lockForUpdate()->findOrFail($rental->id); $this->assertApprovedAndValid($rental); if ($rental->payment?->status !== 'PAID') throw ValidationException::withMessages(['payment' => 'Pembayaran harus LUNAS sebelum handover.']); if ($rental->items->contains(fn (RentalItem $item) => $item->unit->status !== 'RESERVED')) throw ValidationException::withMessages(['rental' => 'Unit tidak lagi dialokasikan.']); $borrowedAt = now(); foreach ($rental->items as $item) { $item->update(['borrowed_at' => $borrowedAt, 'due_at' => $borrowedAt->copy()->addDays(5)]); $item->unit->update(['status' => 'BORROWED']); } $rental->update(['status' => 'BORROWED']); return $rental->fresh('items.unit', 'payment'); });
    }

    public function returnItem(RentalItem $item, string $condition): RentalItem
    {
        return DB::transaction(function () use ($item, $condition) { $item = RentalItem::with('rental', 'unit')->lockForUpdate()->findOrFail($item->id); if (!$item->borrowed_at || $item->returned_at) throw ValidationException::withMessages(['item' => 'Unit bukan pinjaman aktif.']); $returnedAt = now(); $secondsLate = max(0, $item->due_at?->diffInSeconds($returnedAt, false) ?? 0); $fine = $secondsLate > 0 ? (int) ceil($secondsLate / 86400) * self::LATE_RATE : 0; $item->update(['returned_at' => $returnedAt, 'return_condition' => $condition, 'fine_amount' => $fine, 'fine_payment_status' => $fine > 0 ? 'UNPAID' : 'NO_FINE']); $item->unit->update(['status' => strtoupper($condition) === 'GOOD' ? 'AVAILABLE' : strtoupper($condition)]); $rental = $item->rental->fresh('items'); if ($rental->items->every(fn (RentalItem $current) => $current->returned_at)) $rental->update(['status' => 'RETURNED']); return $item->fresh('rental', 'unit'); });
    }

    public function payFine(RentalItem $item, int $adminId): RentalItem
    {
        return DB::transaction(function () use ($item, $adminId) { $item = RentalItem::lockForUpdate()->findOrFail($item->id); if ((float) $item->fine_amount <= 0 || $item->fine_payment_status !== 'UNPAID') throw ValidationException::withMessages(['fine' => 'Denda tidak dapat dilunasi pada status ini.']); $item->update(['fine_payment_status' => 'PAID', 'fine_paid_at' => now(), 'fine_confirmed_by' => $adminId]); return $item->fresh(); });
    }

    private function assertApprovedAndValid(Rental $rental): void { if ($rental->status !== 'APPROVED') throw ValidationException::withMessages(['rental' => 'Rental harus APPROVED.']); if ($rental->expires_at && $rental->expires_at->lte(now())) { $rental->update(['status' => 'EXPIRED']); $this->releaseUnits($rental); throw ValidationException::withMessages(['rental' => 'Batas pengambilan sudah lewat.']); } }
    private function releaseUnits(Rental $rental): void { $rental->loadMissing('items.unit'); foreach ($rental->items as $item) { if ($item->unit && $item->unit->status === 'RESERVED') $item->unit->update(['status' => 'AVAILABLE']); } }
}
