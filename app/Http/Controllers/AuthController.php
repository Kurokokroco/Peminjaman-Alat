<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function frmLogin()
    {
        return view('login');
    }

    public function prosesLogin(Request $request)
    {
        $data = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($data)) {
            $request->session()->regenerate();

            LogAktivitas::catat('login ke aplikasi');

            return redirect()->route('dashboard');
        }

        return back()->withErrors(['username' => 'username atau password salah'])->withInput();
    }

    public function logout(Request $request)
    {
        LogAktivitas::catat('logout dari aplikasi');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
