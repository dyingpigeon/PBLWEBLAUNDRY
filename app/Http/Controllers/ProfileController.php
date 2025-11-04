<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function index()
    {
        Log::debug('ProfileController@index: Memulai proses menampilkan halaman profile');
        
        $user = auth()->user();
        Log::debug('ProfileController@index: User data', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email
        ]);
        
        // Hitung statistik profile
        Log::debug('ProfileController@index: Memulai perhitungan statistik profile');
        $stats = $this->getProfileStats($user->id);
        Log::debug('ProfileController@index: Statistik profile berhasil dihitung', $stats);
        
        Log::debug('ProfileController@index: Proses selesai, mengembalikan view profile');
        return view('profile.index', compact('user', 'stats'));
    }

    public function update(Request $request)
    {
        Log::debug('ProfileController@update: Memulai proses update profile');
        Log::debug('ProfileController@update: Data request received', [
            'name' => $request->name,
            'email' => $request->email
        ]);

        $user = auth()->user();
        Log::debug('ProfileController@update: User yang akan diupdate', [
            'user_id' => $user->id,
            'current_name' => $user->name,
            'current_email' => $user->email
        ]);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            Log::warning('ProfileController@update: Validasi gagal', [
                'errors' => $validator->errors()->toArray(),
                'input_data' => $request->all()
            ]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Log::debug('ProfileController@update: Validasi berhasil');

        try {
            Log::debug('ProfileController@update: Menyimpan perubahan profile ke database');
            $affectedRows = DB::update("
                UPDATE users 
                SET name = ?, email = ?, updated_at = NOW() 
                WHERE id = ?
            ", [
                $request->name,
                $request->email,
                $user->id
            ]);

            Log::debug('ProfileController@update: Update profile selesai', [
                'affected_rows' => $affectedRows,
                'new_name' => $request->name,
                'new_email' => $request->email
            ]);

            return redirect()->route('profile.index')
                ->with('success', 'Profil berhasil diperbarui!');
                
        } catch (\Exception $e) {
            Log::error('ProfileController@update: Gagal update profile', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'update_data' => $request->all()
            ]);
            return redirect()->back()
                ->with('error', 'Gagal memperbarui profil: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function updatePassword(Request $request)
    {
        Log::debug('ProfileController@updatePassword: Memulai proses update password');
        Log::debug('ProfileController@updatePassword: Data request received (password fields hidden untuk security)');

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            Log::warning('ProfileController@updatePassword: Validasi password gagal', [
                'errors' => $validator->errors()->toArray()
            ]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Log::debug('ProfileController@updatePassword: Validasi password berhasil');

        $user = auth()->user();
        Log::debug('ProfileController@updatePassword: Memverifikasi current password');

        // Cek password lama
        if (!Hash::check($request->current_password, $user->password)) {
            Log::warning('ProfileController@updatePassword: Current password salah', [
                'user_id' => $user->id,
                'ip_address' => $request->ip()
            ]);
            return redirect()->back()
                ->with('error', 'Password saat ini salah!')
                ->withInput();
        }

        Log::debug('ProfileController@updatePassword: Current password valid');

        try {
            Log::debug('ProfileController@updatePassword: Menyimpan password baru ke database');
            $affectedRows = DB::update("
                UPDATE users 
                SET password = ?, updated_at = NOW() 
                WHERE id = ?
            ", [
                Hash::make($request->password),
                $user->id
            ]);

            Log::debug('ProfileController@updatePassword: Update password selesai', [
                'affected_rows' => $affectedRows,
                'user_id' => $user->id
            ]);

            return redirect()->route('profile.index')
                ->with('success', 'Password berhasil diubah!');
                
        } catch (\Exception $e) {
            Log::error('ProfileController@updatePassword: Gagal update password', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Gagal mengubah password: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Request $request)
    {
        Log::debug('ProfileController@destroy: Memulai proses hapus akun');
        Log::debug('ProfileController@destroy: Data request received (password verification)');

        $validator = Validator::make($request->all(), [
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            Log::warning('ProfileController@destroy: Validasi password untuk hapus akun gagal', [
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Password harus diisi!'
            ], 422);
        }

        $user = auth()->user();
        Log::debug('ProfileController@destroy: Memverifikasi password untuk hapus akun', [
            'user_id' => $user->id,
            'user_email' => $user->email
        ]);

        // Verifikasi password
        if (!Hash::check($request->password, $user->password)) {
            Log::warning('ProfileController@destroy: Password verifikasi gagal untuk hapus akun', [
                'user_id' => $user->id,
                'ip_address' => $request->ip()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Password salah!'
            ], 422);
        }

        Log::debug('ProfileController@destroy: Password verifikasi berhasil, melanjutkan proses hapus akun');

        try {
            // Hapus user
            Log::debug('ProfileController@destroy: Menghapus user dari database', ['user_id' => $user->id]);
            $deletedRows = DB::delete("DELETE FROM users WHERE id = ?", [$user->id]);
            Log::debug('ProfileController@destroy: Hapus user selesai', ['deleted_rows' => $deletedRows]);

            // Logout user
            Log::debug('ProfileController@destroy: Melakukan logout user');
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            Log::debug('ProfileController@destroy: Session cleared dan token regenerated');

            Log::debug('ProfileController@destroy: Akun berhasil dihapus', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Akun berhasil dihapus!',
                'redirect_url' => url('/')
            ]);
                
        } catch (\Exception $e) {
            Log::error('ProfileController@destroy: Gagal menghapus akun', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus akun: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStats()
    {
        Log::debug('ProfileController@getStats: Memulai proses mengambil statistik via API');
        
        $user = auth()->user();
        Log::debug('ProfileController@getStats: User request', [
            'user_id' => $user->id,
            'user_name' => $user->name
        ]);

        $stats = $this->getProfileStats($user->id);
        Log::debug('ProfileController@getStats: Statistik berhasil diambil', $stats);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    private function getProfileStats($userId)
    {
        Log::debug('ProfileController@getProfileStats: Memulai perhitungan statistik profile', ['user_id' => $userId]);

        $stats = [
            'total_transactions' => 0,
            'today_transactions' => 0,
            'month_revenue' => 0,
            'total_customers' => 0,
        ];

        try {
            // Cek apakah tabel transactions ada
            Log::debug('ProfileController@getProfileStats: Memeriksa keberadaan table transactions');
            $transactionsTableExists = DB::selectOne("
                SELECT COUNT(*) as count 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = 'transactions'
            ");

            Log::debug('ProfileController@getProfileStats: Table transactions exists', [
                'exists' => $transactionsTableExists->count > 0
            ]);

            if ($transactionsTableExists->count > 0) {
                // Total transactions
                Log::debug('ProfileController@getProfileStats: Menghitung total transactions');
                $totalTransactions = DB::selectOne("
                    SELECT COUNT(*) as count FROM transactions
                ");
                $stats['total_transactions'] = $totalTransactions->count ?? 0;
                Log::debug('ProfileController@getProfileStats: Total transactions', [
                    'count' => $stats['total_transactions']
                ]);

                // Today's transactions
                Log::debug('ProfileController@getProfileStats: Menghitung transactions hari ini');
                $todayTransactions = DB::selectOne("
                    SELECT COUNT(*) as count FROM transactions 
                    WHERE DATE(created_at) = CURDATE()
                ");
                $stats['today_transactions'] = $todayTransactions->count ?? 0;
                Log::debug('ProfileController@getProfileStats: Today transactions', [
                    'count' => $stats['today_transactions']
                ]);

                // This month revenue
                Log::debug('ProfileController@getProfileStats: Menghitung revenue bulan ini');
                $monthRevenue = DB::selectOne("
                    SELECT COALESCE(SUM(total_amount), 0) as total 
                    FROM transactions 
                    WHERE MONTH(created_at) = MONTH(CURDATE()) 
                    AND YEAR(created_at) = YEAR(CURDATE())
                ");
                $stats['month_revenue'] = $monthRevenue->total ?? 0;
                Log::debug('ProfileController@getProfileStats: Month revenue', [
                    'revenue' => $stats['month_revenue']
                ]);
            } else {
                Log::debug('ProfileController@getProfileStats: Table transactions tidak ditemukan, menggunakan default values');
            }

            // Cek apakah tabel customers ada
            Log::debug('ProfileController@getProfileStats: Memeriksa keberadaan table customers');
            $customersTableExists = DB::selectOne("
                SELECT COUNT(*) as count 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = 'customers'
            ");

            Log::debug('ProfileController@getProfileStats: Table customers exists', [
                'exists' => $customersTableExists->count > 0
            ]);

            if ($customersTableExists->count > 0) {
                // Total customers
                Log::debug('ProfileController@getProfileStats: Menghitung total customers');
                $totalCustomers = DB::selectOne("
                    SELECT COUNT(*) as count FROM customers
                ");
                $stats['total_customers'] = $totalCustomers->count ?? 0;
                Log::debug('ProfileController@getProfileStats: Total customers', [
                    'count' => $stats['total_customers']
                ]);
            } else {
                Log::debug('ProfileController@getProfileStats: Table customers tidak ditemukan, menggunakan default values');
            }

        } catch (\Exception $e) {
            Log::error('ProfileController@getProfileStats: Error saat mengambil statistik', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Jika tabel tidak ada, return default values
        }

        Log::debug('ProfileController@getProfileStats: Perhitungan statistik selesai', $stats);
        return $stats;
    }
}