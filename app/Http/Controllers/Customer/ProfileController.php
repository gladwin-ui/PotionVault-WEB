<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit() { return view('customer.profile'); }
    public function update(Request $request) { $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($request->user())], 'phone' => ['nullable', 'string', 'max:30'], 'address' => ['nullable', 'string', 'max:500']]); $request->user()->update(['name' => $data['name'], 'email' => $data['email']]); $request->user()->profile()->updateOrCreate([], ['phone' => $data['phone'] ?? null, 'address' => $data['address'] ?? null]); return back()->with('status', 'Profil berhasil diperbarui.'); }
}
