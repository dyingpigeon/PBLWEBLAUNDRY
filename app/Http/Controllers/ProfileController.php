<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Hitung statistik profile
        $stats = $this->getProfileStats($user->id);
        
        return view('profile.index', compact('user', 'stats'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::update("
                UPDATE users 
                SET name = ?, email = ?, phone = ?, updated_at = NOW() 
                WHERE id = ?
            ", [
                $request->name,
                $request->email,
                $request->phone,
                $user->id
            ]);

            return redirect()->route('profile.index')
                ->with('success', 'Profil berhasil diperbarui!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui profil: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed', // Sesuai dengan name di modal
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = auth()->user();

        // Cek password lama
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->with('error', 'Password saat ini salah!')
                ->withInput();
        }

        try {
            DB::update("
                UPDATE users 
                SET password = ?, updated_at = NOW() 
                WHERE id = ?
            ", [
                Hash::make($request->password), // Sesuai dengan name 'password'
                $user->id
            ]);

            return redirect()->route('profile.index')
                ->with('success', 'Password berhasil diubah!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengubah password: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Password harus diisi!'
            ], 422);
        }

        $user = auth()->user();

        // Verifikasi password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password salah!'
            ], 422);
        }

        try {
            // Hapus user
            DB::delete("DELETE FROM users WHERE id = ?", [$user->id]);

            // Logout user
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'success' => true,
                'message' => 'Akun berhasil dihapus!',
                'redirect_url' => url('/')
            ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus akun: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStats()
    {
        $user = auth()->user();
        $stats = $this->getProfileStats($user->id);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    private function getProfileStats($userId)
    {
        $stats = [
            'total_transactions' => 0,
            'today_transactions' => 0,
            'month_revenue' => 0,
            'total_customers' => 0,
        ];

        try {
            // Total transactions
            $totalTransactions = DB::selectOne("
                SELECT COUNT(*) as count FROM transactions
            ");
            $stats['total_transactions'] = $totalTransactions->count ?? 0;

            // Today's transactions
            $todayTransactions = DB::selectOne("
                SELECT COUNT(*) as count FROM transactions 
                WHERE DATE(created_at) = CURDATE()
            ");
            $stats['today_transactions'] = $todayTransactions->count ?? 0;

            // This month revenue
            $monthRevenue = DB::selectOne("
                SELECT COALESCE(SUM(total_amount), 0) as total 
                FROM transactions 
                WHERE MONTH(created_at) = MONTH(CURDATE()) 
                AND YEAR(created_at) = YEAR(CURDATE())
            ");
            $stats['month_revenue'] = $monthRevenue->total ?? 0;

            // Total customers
            $totalCustomers = DB::selectOne("
                SELECT COUNT(*) as count FROM customers
            ");
            $stats['total_customers'] = $totalCustomers->count ?? 0;

        } catch (\Exception $e) {
            // Jika tabel tidak ada, return default values
            \Log::error('Error getting profile stats: ' . $e->getMessage());
        }

        return $stats;
    }
}