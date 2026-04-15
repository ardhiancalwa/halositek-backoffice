<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): Factory|View
    {
        return view('admin.pages.auth.login');
    }

    public function showRegister(): Factory|View
    {
        return view('admin.pages.auth.register');
    }

    public function showDashboard(): Factory|View
    {
        return view('admin.pages.dashboard.index');
    }

    public function showDesigns(): Factory|View
    {
        return view('admin.pages.dashboard.design.index');
    }

    public function showConsultations(): Factory|View
    {
        return view('admin.pages.dashboard.consultations.index');
    }
}
