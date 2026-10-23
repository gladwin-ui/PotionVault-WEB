<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index() { return view('admin.categories', ['categories' => Category::withCount('units')->orderBy('name')->get()]); }
    public function store(Request $request) { $data = $request->validate(['name' => ['required','string','max:100','unique:categories,name'], 'description' => ['nullable','string']]); Category::create($data); return back()->with('status', 'Kategori berhasil ditambahkan.'); }
    public function update(Request $request, Category $category) { $data = $request->validate(['name' => ['required','string','max:100','unique:categories,name,'.$category->id], 'description' => ['nullable','string']]); $category->update($data); return back()->with('status', 'Kategori berhasil diperbarui.'); }
    public function destroy(Category $category) { if ($category->units()->exists()) return back()->withErrors(['category' => 'Kategori yang masih dipakai unit tidak dapat dihapus.']); $category->delete(); return back()->with('status', 'Kategori berhasil dihapus.'); }
}
