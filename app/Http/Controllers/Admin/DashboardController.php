<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Models\RentalItem;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = ['total' => Unit::count(), 'available' => Unit::where('status', 'AVAILABLE')->count(), 'active' => RentalItem::whereNull('returned_at')->whereHas('rental', fn ($q) => $q->where('status', 'BORROWED'))->count(), 'overdue' => RentalItem::whereNull('returned_at')->whereNotNull('due_at')->where('due_at', '<', now())->count(), 'pending' => Rental::where('status', 'PENDING')->count(), 'customers' => User::where('role', 'user')->count(), 'revenue' => Rental::whereHas('payment', fn ($q) => $q->where('status', 'PAID'))->sum('rental_total'), 'fines' => RentalItem::where('fine_payment_status', 'UNPAID')->sum('fine_amount')];
        $start = Carbon::now()->startOfMonth()->subMonths(5);
        $months = collect(range(0, 5))->map(fn ($offset) => $start->copy()->addMonths($offset));
        $trendLabels = $months->map(fn ($month) => $month->format('M'))->values();
        $trendRentals = $months->map(fn ($month) => Rental::whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])->count())->values();
        return view('admin.dashboard', compact('stats', 'trendLabels', 'trendRentals') + ['recent' => Rental::with('user', 'items.unit', 'payment')->latest()->take(8)->get()]);
    }
}
