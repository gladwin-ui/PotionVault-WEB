<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class BillingController extends Controller
{
    public function index(\Illuminate\Http\Request $request) { $rentals = $request->user()->rentals()->with('items', 'payment')->latest()->get(); $unpaidRentalTotal = $rentals->sum(fn ($rental) => $rental->payment?->status === 'UNPAID' ? (float) $rental->rental_total : 0); $unpaidFineTotal = $rentals->flatMap->items->sum(fn ($item) => $item->fine_payment_status === 'UNPAID' ? (float) $item->fine_amount : 0); return view('customer.billing', compact('rentals', 'unpaidRentalTotal', 'unpaidFineTotal')); }
}
