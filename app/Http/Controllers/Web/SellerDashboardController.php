<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SellerDashboardController extends Controller
{
    public function index()
    {
        // View này sẽ được bảo vệ bởi middleware 'role:seller,admin'
        // Dữ liệu sẽ được Alpine.js tải thông qua API
        return view('seller.dashboard');
    }
}