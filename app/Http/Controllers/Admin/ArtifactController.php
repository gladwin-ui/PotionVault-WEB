<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArtifactController extends Controller
{
    public function index(Request $request) { $items = Unit::with('categories')->when($request->filled('search'), fn ($q) => $q->where(fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%')->orWhere('unit_code', 'like', '%'.$request->string('search').'%')))->when($request->filled('rarity'), fn ($q) => $q->where('rarity', $request->string('rarity')))->latest()->get(); return view('admin.resource', ['title' => 'Manajemen Artefak', 'description' => 'Kelola setiap unit fisik, kode, kondisi, dan harga sewa.', 'items' => $items, 'columns' => ['Kode unit', 'Nama artefak', 'Rarity', 'Kondisi', 'Status']]); }
    public function create() { return view('admin.artifact-form', ['unit' => new Unit, 'categories' => Category::orderBy('name')->get()]); }
    public function edit(Unit $unit) { $unit->load('categories'); return view('admin.artifact-form', ['unit' => $unit, 'categories' => Category::orderBy('name')->get()]); }
    public function store(Request $request) { $data = $this->validated($request); $unit = Unit::create($data); $unit->categories()->sync($request->input('category_ids', [])); return redirect()->route('admin.artifacts.index')->with('status', 'Artefak berhasil ditambahkan.'); }
    public function update(Request $request, Unit $unit) { $data = $this->validated($request, $unit); $unit->update($data); $unit->categories()->sync($request->input('category_ids', [])); return redirect()->route('admin.artifacts.index')->with('status', 'Artefak berhasil diperbarui.'); }
    public function destroy(Unit $unit) { if ($unit->rentalItems()->whereNull('returned_at')->exists()) return back()->withErrors(['unit' => 'Unit dengan rental aktif tidak dapat dinonaktifkan.']); $unit->update(['status' => 'DEACTIVATED']); $unit->delete(); return back()->with('status', 'Artefak berhasil dinonaktifkan.'); }
    private function validated(Request $request, ?Unit $unit = null): array { $data = $request->validate(['name' => ['required', 'string', 'max:150'], 'unit_code' => ['required', 'string', 'max:50', 'unique:units,unit_code,'.($unit?->id ?? 'NULL')], 'description' => ['required', 'string'], 'magical_effect' => ['nullable', 'string'], 'rental_price' => ['required', 'numeric', 'min:0'], 'rarity' => ['required', 'in:Common,Rare,Epic,Legendary'], 'condition' => ['required', 'in:GOOD,MAINTENANCE,DAMAGED'], 'image' => ['nullable', 'image', 'max:5120'], 'category_ids' => ['required', 'array', 'min:1'], 'category_ids.*' => ['integer', 'exists:categories,id']]); if ($request->hasFile('image')) $data['image'] = $request->file('image')->store('artifacts', 'public'); return $data; }
}
