<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\RentalItem;

class HomeController extends Controller
{
    public function index() { return view('customer.home', ['featured' => Unit::query()->where('status', 'AVAILABLE')->take(5)->get()]); }
    public function dashboard(\Illuminate\Http\Request $request)
    {
        $user = $request->user();
        $rentals = $user->rentals()->with('items.unit', 'payment')->latest()->get();
        $activeItems = RentalItem::whereHas('rental', fn ($q) => $q->where('user_id', $user->id)->whereIn('status', ['PENDING', 'APPROVED', 'BORROWED']))->whereNull('returned_at')->get();
        $nearestDueAt = $activeItems->whereNotNull('due_at')->sortBy('due_at')->first()?->due_at;

        return view('customer.dashboard', [
            'rentals' => $rentals,
            'stats' => [
                'active' => $activeItems->count(),
                'active_rentals' => $rentals->whereIn('status', ['APPROVED', 'BORROWED'])->count(),
                'pending' => $rentals->whereIn('status', ['PENDING', 'APPROVED'])->count(),
                'fines' => $rentals->flatMap->items->where('fine_payment_status', 'UNPAID')->sum('fine_amount'),
                'nearest_due_at' => $nearestDueAt,
            ],
        ]);
    }
}
