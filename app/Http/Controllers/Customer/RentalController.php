<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\RentalWorkflowService;
use App\Models\Unit;

class RentalController extends Controller
{
    public function create(Request $request) { $units = Unit::where('status', 'AVAILABLE')->when($request->filled('unit_id'), fn ($q) => $q->whereKey($request->integer('unit_id')))->get(); return view('customer.rental-request', compact('units')); }
    public function store(Request $request, RentalWorkflowService $workflow) { $data = $request->validate(['unit_ids' => ['required', 'array', 'min:1', 'max:2'], 'unit_ids.*' => ['integer', 'distinct', 'exists:units,id']]); $rental = $workflow->request($request->user()->id, $data['unit_ids']); return redirect()->route('rentals.show', $rental)->with('status', 'Pengajuan rental berhasil dikirim.'); }
    public function index(Request $request) { return view('customer.rentals', ['rentals' => $request->user()->rentals()->with('items.unit')->latest()->get()]); }
    public function show(Request $request, $rental) { return view('customer.rental-detail', ['rental' => $request->user()->rentals()->with('items.unit', 'payment')->findOrFail($rental)]); }
    public function cancel(Request $request, $rental, RentalWorkflowService $workflow) { $ownedRental = $request->user()->rentals()->findOrFail($rental); $workflow->cancelByCustomer($ownedRental, $request->user()->id); return redirect()->route('rentals.index')->with('status', 'Pengajuan rental berhasil dibatalkan.'); }
}
