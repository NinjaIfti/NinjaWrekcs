<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard');
    }

    public function orders(): View
    {
        return view('admin.orders');
    }

    public function products(): View
    {
        return view('admin.products');
    }

    public function visitors(): View
    {
        return view('admin.visitors');
    }

    public function financial(): View
    {
        return view('admin.financial');
    }
}

