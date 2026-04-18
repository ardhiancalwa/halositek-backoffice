<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): Factory|View
    {
        return view('admin.pages.auth.login');
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

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors(['email' => 'The provided credentials are incorrect.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Login successful.');
    }
}
