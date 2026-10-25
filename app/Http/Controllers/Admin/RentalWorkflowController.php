<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Models\RentalItem;
use App\Services\RentalWorkflowService;
use Illuminate\Http\Request;

class RentalWorkflowController extends Controller
{
    public function requests() { return view('admin.requests', ['rentals' => Rental::with('user', 'items.unit', 'payment')->latest()->get()]); }
    public function approve(Rental $rental, RentalWorkflowService $workflow) { $workflow->approve($rental, auth()->id()); return back()->with('status', 'Rental berhasil disetujui.'); }
    public function reject(Request $request, Rental $rental, RentalWorkflowService $workflow) { $workflow->reject($rental, auth()->id(), $request->input('reason')); return back()->with('status', 'Rental berhasil ditolak.'); }
    public function payment(Rental $rental, RentalWorkflowService $workflow) { $workflow->confirmPayment($rental, auth()->id()); return back()->with('status', 'Pembayaran berhasil dikonfirmasi.'); }
    public function refund(Request $request, Rental $rental, RentalWorkflowService $workflow) { $workflow->resolveRefund($rental, auth()->id(), $request->input('refund_notes')); return back()->with('status', 'Refund berhasil dicatat.'); }
    public function handover(Rental $rental, RentalWorkflowService $workflow) { $workflow->handover($rental); return back()->with('status', 'Serah terima berhasil dikonfirmasi.'); }
    public function returnItem(Request $request, RentalItem $item, RentalWorkflowService $workflow) { $data = $request->validate(['condition' => ['required', 'in:GOOD,MAINTENANCE,DAMAGED'], 'return_notes' => ['nullable', 'string']]); $returned = $workflow->returnItem($item, $data['condition']); if (!empty($data['return_notes'])) $returned->update(['return_notes' => $data['return_notes']]); return back()->with('status', 'Pengembalian berhasil dikonfirmasi.'); }
    public function payFine(RentalItem $item, RentalWorkflowService $workflow) { $workflow->payFine($item, auth()->id()); return back()->with('status', 'Pelunasan denda berhasil dikonfirmasi.'); }
    public function report(Request $request)
    {
        $status = $request->string('status')->toString();
        $query = Rental::with('user', 'items.unit', 'payment')
            ->when($status && $status !== 'OVERDUE', fn ($q) => $q->where('status', $status))
            ->when($status === 'OVERDUE', fn ($q) => $q->whereHas('items', fn ($item) => $item->whereNull('returned_at')->whereNotNull('due_at')->where('due_at', '<', now())))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('user_id', $request->integer('customer_id')))
            ->when($request->filled('artifact'), fn ($q) => $q->whereHas('items.unit', fn ($unit) => $unit->where('name', 'like', '%'.$request->string('artifact').'%')->orWhere('unit_code', 'like', '%'.$request->string('artifact').'%')))
            ->when($request->filled('payment_status'), fn ($q) => $q->whereHas('payment', fn ($payment) => $payment->where('status', $request->string('payment_status'))))
            ->when($request->filled('fine_status'), fn ($q) => $q->whereHas('items', fn ($item) => $item->where('fine_payment_status', $request->string('fine_status'))))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('date_to')));

        $rentals = $query->latest()->get();
        $totals = [
            'rental' => $rentals->sum('rental_total'),
            'confirmed' => $rentals->filter(fn ($rental) => $rental->payment?->status === 'PAID')->sum('rental_total'),
            'refunded' => $rentals->filter(fn ($rental) => $rental->payment?->status === 'REFUNDED')->sum('rental_total'),
            'unpaid_fines' => $rentals->flatMap->items->where('fine_payment_status', 'UNPAID')->sum('fine_amount'),
            'paid_fines' => $rentals->flatMap->items->where('fine_payment_status', 'PAID')->sum('fine_amount'),
        ];
        return view('admin.report', ['rentals' => $rentals, 'customers' => \App\Models\User::where('role', 'user')->orderBy('name')->get(), 'totals' => $totals]);
    }
}
