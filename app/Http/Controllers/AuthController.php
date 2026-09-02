<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister() { return view('auth.register'); }
    public function showLogin() { return view('auth.login'); }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'bagian' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|min:6',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['status'] = 'user';
        $data['permission'] = 'pending';

        User::create($data);

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Menunggu persetujuan Admin.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            if ($user->permission == 'pending') {
                return back()->withErrors(['username' => 'Akun Anda belum disetujui (Status: ' . $user->permission . ').']);
            } elseif ($user->permission == 'rejected'){
                return back()->withErrors(['username' => 'Akun Anda ditolak, silahkan hubungi admin.']);
            }
            Auth::login($user);
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['username' => 'Kredensial tidak valid.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}