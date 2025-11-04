<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Tentukan waktu berdasarkan jam saat ini
        $currentTime = Carbon::now();
        $hour1 = $currentTime->hour;
        $hour = $hour1 + 7;
        // $hour = $currentTime->hour;

        if ($hour < 12) {
            $waktu = 'Pagi';
        } elseif ($hour < 15) {
            $waktu = 'Siang';
        } elseif ($hour < 19) {
            $waktu = 'Sore';
        } else {
            $waktu = 'Malam';
        }

        return view('dashboard.index', [
            'user' => $user,
            'waktu' => $waktu,
            'debug_hour' => $hour,
            'debug_time' => $currentTime->format('H:i:s'),
            'debug_full_time' => $currentTime->format('Y-m-d H:i:s')
        ]);
    }
}