<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Fallback jika form login disubmit secara native POST (tanpa JavaScript/Livewire)
Route::post('/login', function (Request $request) {
    $data = $request->input('data', []);
    $email = $request->input('email', $data['email'] ?? null);
    $password = $request->input('password', $data['password'] ?? null);
    $remember = (bool) $request->input('remember', $data['remember'] ?? false);

    if (!$email || !$password) {
        return back()->withErrors([
            'email' => 'Email dan kata sandi wajib diisi.',
        ])->withInput();
    }

    if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
        $request->session()->regenerate();
        return redirect()->intended('/');
    }

    return back()->withErrors([
        'email' => 'Email atau kata sandi tidak sesuai.',
    ])->withInput();
});
