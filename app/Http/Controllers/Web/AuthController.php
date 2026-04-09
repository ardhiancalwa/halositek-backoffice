<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): Factory|View
    {
        return view('auth.login');
    }

    public function showRegister(): Factory|View
    {
        return view('auth.register');
    }

    public function showDashboard(): Factory|View
    {
        return view('dashboard.index');
    }

    public function showDesigns()
    {
        return view('dashboard.design');
    }
}
