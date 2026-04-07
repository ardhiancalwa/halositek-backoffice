<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }
}
