<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class AiBotsController extends Controller
{
    public function index(): Factory|View
    {
        return view('admin.pages.dashboard.ai-bots.index');
    }
}
