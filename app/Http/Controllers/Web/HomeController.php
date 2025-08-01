<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the home page with products and banners.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // dd(config(key: 'app.api_url'));
        return Inertia::render('Home');
    }
}
