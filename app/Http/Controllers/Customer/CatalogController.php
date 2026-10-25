<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Category;

class CatalogController extends Controller
{
    public function index(\Illuminate\Http\Request $request) { $units = Unit::with('categories')->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))->when($request->filled('rarity'), fn ($query) => $query->where('rarity', $request->string('rarity')))->when($request->filled('category_id'), fn ($query) => $query->whereHas('categories', fn ($category) => $category->whereKey($request->integer('category_id'))))->latest()->paginate(8)->withQueryString(); $categories = Category::orderBy('name')->get(); return view('customer.catalog', compact('units', 'categories')); }
    public function show(Unit $unit) { return view('customer.detail', compact('unit')); }
}
