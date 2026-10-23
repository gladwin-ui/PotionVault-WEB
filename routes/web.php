<?php

use App\Http\Controllers\Customer\BillingController;
use App\Http\Controllers\Customer\CatalogController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Customer\RentalController as CustomerRentalController;
use App\Http\Controllers\Admin\ArtifactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\RentalWorkflowController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog');
Route::get('/catalog/{unit}', [CatalogController::class, 'show'])->name('catalog.show');
Route::middleware('guest')->group(function () { Route::get('/login', [AuthController::class, 'login'])->name('login'); Route::post('/login', [AuthController::class, 'authenticate'])->name('login.store'); Route::get('/register', [AuthController::class, 'register'])->name('register'); Route::post('/register', [AuthController::class, 'store'])->name('register.store'); });
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
    Route::get('/rental-request', [CustomerRentalController::class, 'create'])->name('rentals.create');
    Route::post('/rental-request', [CustomerRentalController::class, 'store'])->name('rentals.store');
    Route::get('/my-rentals', [CustomerRentalController::class, 'index'])->name('rentals.index');
    Route::get('/my-rentals/{rental}', [CustomerRentalController::class, 'show'])->name('rentals.show');
    Route::post('/my-rentals/{rental}/cancel', [CustomerRentalController::class, 'cancel'])->name('rentals.cancel');
    Route::get('/billing', [BillingController::class, 'index'])->name('billing');
    Route::get('/profile', [CustomerProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [CustomerProfileController::class, 'update'])->name('profile.update');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/artifacts', [ArtifactController::class, 'index'])->name('artifacts.index');
    Route::get('/artifacts/create', [ArtifactController::class, 'create'])->name('artifacts.create');
    Route::post('/artifacts', [ArtifactController::class, 'store'])->name('artifacts.store');
    Route::get('/artifacts/{unit}/edit', [ArtifactController::class, 'edit'])->name('artifacts.edit');
    Route::put('/artifacts/{unit}', [ArtifactController::class, 'update'])->name('artifacts.update');
    Route::delete('/artifacts/{unit}', [ArtifactController::class, 'destroy'])->name('artifacts.destroy');
    Route::get('/requests', [RentalWorkflowController::class, 'requests'])->name('requests');
    Route::post('/requests/{rental}/approve', [RentalWorkflowController::class, 'approve'])->name('requests.approve');
    Route::post('/requests/{rental}/reject', [RentalWorkflowController::class, 'reject'])->name('requests.reject');
    Route::post('/payments/{rental}/confirm', [RentalWorkflowController::class, 'payment'])->name('payments.confirm');
    Route::post('/handover/{rental}', [RentalWorkflowController::class, 'handover'])->name('handover.confirm');
    Route::post('/returns/{item}', [RentalWorkflowController::class, 'returnItem'])->name('returns.confirm');
    Route::post('/fines/{item}/pay', [RentalWorkflowController::class, 'payFine'])->name('fines.pay');
    Route::get('/reports', [RentalWorkflowController::class, 'report'])->name('reports');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories'); Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store'); Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update'); Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers'); Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store'); Route::put('/customers/{user}', [CustomerController::class, 'update'])->name('customers.update'); Route::post('/customers/{user}/toggle', [CustomerController::class, 'toggle'])->name('customers.toggle');
    foreach (['payments', 'handover', 'returns', 'fines'] as $resource) Route::get('/'.$resource, [ResourceController::class, 'show'])->defaults('resource', $resource)->name($resource);
    Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
});
