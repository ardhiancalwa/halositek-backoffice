<?php

namespace App\Http\Controllers\Web\Client;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function home(): Factory|View
    {
        return view('client.pages.home');
    }

    public function about(): Factory|View
    {
        return view('client.pages.about');
    }

    public function download(): Factory|View
    {
        return view('client.pages.download');
    }
}
