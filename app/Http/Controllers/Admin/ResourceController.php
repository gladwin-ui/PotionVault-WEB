<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Models\RentalItem;

class ResourceController extends Controller
{
    public function show(string $resource) {
        $query = Rental::with('user','items.unit','payment')->latest();
        $items = match ($resource) { 'payments' => $query->whereHas('payment', fn ($q) => $q->whereIn('status', ['UNPAID', 'REFUND_PENDING']))->get(), 'handover' => $query->where('status','APPROVED')->whereHas('payment', fn ($q) => $q->where('status','PAID'))->get(), 'returns' => RentalItem::with('rental.user','unit')->whereHas('rental', fn ($q) => $q->where('status','BORROWED'))->whereNull('returned_at')->latest()->get(), 'fines' => RentalItem::with('rental.user','unit')->where('fine_payment_status','UNPAID')->where('fine_amount','>',0)->latest()->get(), default => collect() };
        return view('admin.operations', compact('resource','items'));
    }
}
