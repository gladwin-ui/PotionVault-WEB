<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit() { return view('admin.profile'); }
    public function update(Request $request) { $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($request->user())]]); $request->user()->update(['name' => $data['name'], 'email' => $data['email']]); return back()->with('status', 'Profil Admin berhasil diperbarui.'); }
}
