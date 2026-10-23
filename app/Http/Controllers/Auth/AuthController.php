<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login() { return view('auth.login'); }
    public function authenticate(Request $request) { $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required']]); if (Auth::attempt(array_merge($credentials, ['is_active' => true]))) { $request->session()->regenerate(); return redirect()->intended(Auth::user()->role === 'admin' ? route('admin.dashboard') : route('dashboard')); } return back()->withErrors(['email' => 'Email atau password tidak sesuai.'])->onlyInput('email'); }
    public function register() { return view('auth.register'); }
    public function store(Request $request) { $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', 'unique:users'], 'phone' => ['required', 'string', 'max:30'], 'password' => ['required', 'min:8', 'confirmed']]); $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password']), 'role' => 'user']); Profile::create(['user_id' => $user->id, 'phone' => $data['phone']]); Auth::login($user); return redirect()->route('dashboard'); }
    public function logout(Request $request) { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('home'); }
}
