<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // View này sẽ được bảo vệ bởi middleware 'role:admin'
        // Dữ liệu sẽ được Alpine.js tải thông qua API
        return view('admin.dashboard');
    }
}