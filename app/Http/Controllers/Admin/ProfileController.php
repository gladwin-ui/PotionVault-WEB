<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit() { return view('admin.profile'); }
    public function update(Request $request) { $data = $request->validate(['name' => ['required', 'string', 'max:120']]); $request->user()->update(['name' => $data['name']]); return back()->with('status', 'Profil Admin berhasil diperbarui.'); }
}
