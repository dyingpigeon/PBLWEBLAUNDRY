<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HeaderController extends Controller
{
    // Get all header data (business name, notifications count, user info)
    public function getHeaderData()
    {
        try {
            // Get business name
            $businessSetting = DB::select("
                SELECT value 
                FROM settings 
                WHERE `key` = 'business_name' AND `group` = 'business' 
                LIMIT 1
            ");

            $businessName = !empty($businessSetting) ? $businessSetting[0]->value : 'LaundryKu';

            // Get unread notifications count - PERBAIKAN: gunakan backticks untuk `read`
            $notificationsCount = DB::select("
                SELECT COUNT(*) as count 
                FROM notifications 
                WHERE `read` = 0 AND user_id = ?
            ", [auth()->id()]);

            $unreadCount = !empty($notificationsCount) ? $notificationsCount[0]->count : 0;

            // Get user data
            $user = auth()->user();

            return response()->json([
                'success' => true,
                'business_name' => $businessName,
                'unread_notifications' => $unreadCount,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'initial' => strtoupper(substr($user->name, 0, 1))
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'business_name' => 'LaundryKu',
                'unread_notifications' => 0,
                'user' => [
                    'name' => auth()->user()->name ?? 'User',
                    'email' => auth()->user()->email ?? '',
                    'initial' => strtoupper(substr(auth()->user()->name ?? 'U', 0, 1))
                ],
                'message' => 'Gagal memuat data header: ' . $e->getMessage()
            ]);
        }
    }

    // Method untuk mendapatkan business name saja
    public function getBusinessName()
    {
        try {
            $setting = DB::select("
                SELECT value 
                FROM settings 
                WHERE `key` = 'business_name' AND `group` = 'business' 
                LIMIT 1
            ");

            $businessName = !empty($setting) ? $setting[0]->value : 'LaundryKu';

            return response()->json([
                'success' => true,
                'business_name' => $businessName
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'business_name' => 'LaundryKu',
                'message' => 'Gagal memuat nama bisnis: ' . $e->getMessage()
            ]);
        }
    }

    // Get notifications for header - PERBAIKAN: juga perbaiki di sini
    public function getNotifications()
    {
        try {
            $notifications = DB::select("
                SELECT id, type, title, message, `read`, created_at
                FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 5
            ", [auth()->id()]);

            $unreadCount = DB::select("
                SELECT COUNT(*) as count 
                FROM notifications 
                WHERE `read` = 0 AND user_id = ?
            ", [auth()->id()]);

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount[0]->count ?? 0
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'notifications' => [],
                'unread_count' => 0,
                'message' => 'Gagal memuat notifikasi: ' . $e->getMessage()
            ]);
        }
    }
}