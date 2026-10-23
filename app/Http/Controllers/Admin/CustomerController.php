<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    public function index() { return view('admin.customers', ['customers' => User::where('role','user')->with('profile')->latest()->get()]); }
    public function store(Request $request) { $data = $request->validate(['name'=>['required','string','max:120'],'email'=>['required','email','unique:users,email'],'phone'=>['required','string','max:30'],'address'=>['nullable','string'],'password'=>['required','min:8','confirmed']]); $user = User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>Hash::make($data['password']),'role'=>'user']); Profile::create(['user_id'=>$user->id,'phone'=>$data['phone'],'address'=>$data['address'] ?? null]); return back()->with('status','Customer berhasil ditambahkan.'); }
    public function update(Request $request, User $user) { abort_unless($user->role === 'user', 404); $data = $request->validate(['name'=>['required','string','max:120'],'email'=>['required','email','unique:users,email,'.$user->id],'phone'=>['nullable','string','max:30'],'address'=>['nullable','string']]); $user->update(['name'=>$data['name'],'email'=>$data['email']]); $user->profile()->updateOrCreate([], ['phone'=>$data['phone'] ?? null,'address'=>$data['address'] ?? null]); return back()->with('status','Data customer berhasil diperbarui.'); }
    public function toggle(User $user) { abort_unless($user->role === 'user', 404); $user->update(['is_active'=>!$user->is_active]); return back()->with('status', 'Customer berhasil '.($user->is_active ? 'diaktifkan.' : 'dinonaktifkan.')); }
}
