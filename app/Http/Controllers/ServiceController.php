<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $totalServices = 8; // Mock data
        return view('services.index', compact('totalServices'));
    }
}