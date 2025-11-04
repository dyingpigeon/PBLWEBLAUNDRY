<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;
use PDO;

class SettingController extends Controller
{
    // Display settings page
    public function index()
    {
        return view('settings.index');
    }

    // Get business settings
    public function getBusinessSettings()
    {
        $settings = DB::select("
            SELECT `key`, value, type 
            FROM settings 
            WHERE `group` = 'business'
        ");

        $businessSettings = [];
        foreach ($settings as $setting) {
            $businessSettings[$setting->key] = $this->castValue($setting->value, $setting->type);
        }

        return response()->json($businessSettings);
    }

    // Save business settings
    public function saveBusinessSettings(Request $request)
    {
        \Log::info('Save Business Settings Started', $request->all());

        try {
            $validated = $request->validate([
                'business_name' => 'required|string|max:255',
                'business_address' => 'nullable|string',
                'business_phone' => 'nullable|string|max:20',
                'business_email' => 'nullable|email|max:255'
            ]);

            \Log::info('Validation passed', $validated);

            // Update business_name
            Setting::updateOrCreate(
                ['key' => 'business_name'],
                [
                    'value' => $validated['business_name'],
                    'type' => 'string',
                    'group' => 'business',
                    'description' => 'Nama usaha laundry'
                ]
            );

            \Log::info('Business name saved');

            // Update other fields...
            Setting::updateOrCreate(
                ['key' => 'business_address'],
                [
                    'value' => $validated['business_address'] ?? '',
                    'type' => 'string',
                    'group' => 'business',
                    'description' => 'Alamat usaha laundry'
                ]
            );

            Setting::updateOrCreate(
                ['key' => 'business_phone'],
                [
                    'value' => $validated['business_phone'] ?? '',
                    'type' => 'string',
                    'group' => 'business',
                    'description' => 'Telepon usaha laundry'
                ]
            );

            Setting::updateOrCreate(
                ['key' => 'business_email'],
                [
                    'value' => $validated['business_email'] ?? '',
                    'type' => 'string',
                    'group' => 'business',
                    'description' => 'Email usaha laundry'
                ]
            );

            \Log::info('All business settings saved successfully');

            return response()->json([
                'success' => true,
                'message' => 'Pengaturan bisnis berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error saving business settings: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pengaturan: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get business hours
// Get business hours - PERBAIKAN
    public function getBusinessHours()
    {
        try {
            // Ambil dari settings table dengan key 'operating_hours'
            $setting = DB::select("
            SELECT value, type 
            FROM settings 
            WHERE `key` = 'operating_hours'
        ");

            if (empty($setting)) {
                // Return default hours jika tidak ada data
                return response()->json($this->getDefaultBusinessHours());
            }

            $hoursData = json_decode($setting[0]->value, true);

            // Convert ke format yang diharapkan JavaScript
            $formattedHours = [];
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

            foreach ($days as $day) {
                $formattedHours[] = [
                    'day' => $day,
                    'open_time' => $hoursData[$day]['open'] ?? '08:00',
                    'close_time' => $hoursData[$day]['close'] ?? '20:00',
                    'is_closed' => $hoursData[$day]['closed'] ?? false
                ];
            }

            return response()->json($formattedHours);

        } catch (\Exception $e) {
            \Log::error('Error getting business hours: ' . $e->getMessage());
            return response()->json($this->getDefaultBusinessHours());
        }
    }

    // Helper untuk default business hours
    private function getDefaultBusinessHours()
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $defaultHours = [];

        foreach ($days as $day) {
            $defaultHours[] = [
                'day' => $day,
                'open_time' => '08:00',
                'close_time' => '20:00',
                'is_closed' => false
            ];
        }

        return $defaultHours;
    }

    // Save business hours
    // Save business hours - PERBAIKAN
    public function saveBusinessHours(Request $request)
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        try {
            DB::beginTransaction();

            $hoursData = [];

            foreach ($days as $day) {
                $isClosed = $request->input("{$day}_closed", false) ? true : false;
                $openTime = $request->input("{$day}_open", '08:00');
                $closeTime = $request->input("{$day}_close", '20:00');

                $hoursData[$day] = [
                    'open' => $openTime,
                    'close' => $closeTime,
                    'closed' => $isClosed
                ];
            }

            // Simpan sebagai JSON di settings table
            DB::statement("
            INSERT INTO settings (`key`, value, type, `group`, description, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE value = ?, updated_at = NOW()
        ", [
                'operating_hours',
                json_encode($hoursData),
                'json',
                'business',
                'Jam operasional laundry per hari',
                json_encode($hoursData)
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jam operasional berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving business hours: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan jam operasional: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get receipt settings
    public function getReceiptSettings()
    {
        $settings = DB::select("
            SELECT `key`, value, type 
            FROM settings 
            WHERE `group` = 'receipt'
        ");

        $receiptSettings = [];
        foreach ($settings as $setting) {
            $receiptSettings[$setting->key] = $this->castValue($setting->value, $setting->type);
        }

        return response()->json($receiptSettings);
    }

    // Save receipt settings
    public function saveReceiptSettings(Request $request)
    {
        $validated = $request->validate([
            'receipt_header' => 'required|string',
            'receipt_footer' => 'required|string',
            'show_logo' => 'boolean',
            'auto_print' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Save receipt header
            DB::statement("
                INSERT INTO settings (`key`, value, type, `group`, description, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE value = ?, updated_at = NOW()
            ", [
                'receipt_header',
                $validated['receipt_header'],
                'string',
                'receipt',
                'Header struk',
                $validated['receipt_header']
            ]);

            // Save receipt footer
            DB::statement("
                INSERT INTO settings (`key`, value, type, `group`, description, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE value = ?, updated_at = NOW()
            ", [
                'receipt_footer',
                $validated['receipt_footer'],
                'string',
                'receipt',
                'Footer struk',
                $validated['receipt_footer']
            ]);

            // Save show logo setting
            DB::statement("
                INSERT INTO settings (`key`, value, type, `group`, description, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE value = ?, updated_at = NOW()
            ", [
                'show_logo',
                $validated['show_logo'] ? '1' : '0',
                'boolean',
                'receipt',
                'Tampilkan logo di struk',
                $validated['show_logo'] ? '1' : '0'
            ]);

            // Save auto print setting
            DB::statement("
                INSERT INTO settings (`key`, value, type, `group`, description, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE value = ?, updated_at = NOW()
            ", [
                'auto_print',
                $validated['auto_print'] ? '1' : '0',
                'boolean',
                'receipt',
                'Auto print struk setelah transaksi',
                $validated['auto_print'] ? '1' : '0'
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Pengaturan struk berhasil disimpan']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan pengaturan struk: ' . $e->getMessage()], 500);
        }
    }

    // Get notification settings
    public function getNotificationSettings()
    {
        $settings = DB::select("
            SELECT `key`, value, type 
            FROM settings 
            WHERE `group` = 'notification'
        ");

        $notificationSettings = [];
        foreach ($settings as $setting) {
            $notificationSettings[$setting->key] = $this->castValue($setting->value, $setting->type);
        }

        return response()->json($notificationSettings);
    }

    // Save notification settings
    public function saveNotificationSettings(Request $request)
    {
        $validated = $request->validate([
            'new_order_notification' => 'boolean',
            'status_change_notification' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Save new order notification setting
            DB::statement("
                INSERT INTO settings (`key`, value, type, `group`, description, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE value = ?, updated_at = NOW()
            ", [
                'new_order_notification',
                $validated['new_order_notification'] ? '1' : '0',
                'boolean',
                'notification',
                'Notifikasi pesanan baru',
                $validated['new_order_notification'] ? '1' : '0'
            ]);

            // Save status change notification setting
            DB::statement("
                INSERT INTO settings (`key`, value, type, `group`, description, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE value = ?, updated_at = NOW()
            ", [
                'status_change_notification',
                $validated['status_change_notification'] ? '1' : '0',
                'boolean',
                'notification',
                'Notifikasi perubahan status',
                $validated['status_change_notification'] ? '1' : '0'
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Pengaturan notifikasi berhasil disimpan']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan pengaturan notifikasi: ' . $e->getMessage()], 500);
        }
    }

    // Perform backup
// Perform backup - VERSION IMPROVED
    public function performBackup(Request $request)
    {
        $backupType = $request->input('type', 'full');
        $userId = auth()->id();

        try {
            $filename = 'backup_' . $backupType . '_' . date('Y-m-d_H-i-s') . '.csv';
            $filePath = 'backups/' . $filename;

            $recordCount = 0;
            $data = '';

            // Daftar table yang tersedia untuk backup
            $availableTables = $this->getAvailableTables();

            switch ($backupType) {
                case 'customers':
                    if (in_array('customers', $availableTables)) {
                        $customers = DB::select("SELECT * FROM customers");
                        $recordCount = count($customers);
                        $data = $this->arrayToCsv($customers);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Table customers tidak tersedia'
                        ], 400);
                    }
                    break;

                case 'orders':
                    if (in_array('orders', $availableTables)) {
                        $orders = DB::select("SELECT * FROM orders");
                        $recordCount = count($orders);
                        $data = $this->arrayToCsv($orders);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Table orders tidak tersedia'
                        ], 400);
                    }
                    break;

                case 'transactions':
                    if (in_array('transactions', $availableTables)) {
                        $transactions = DB::select("SELECT * FROM transactions");
                        $recordCount = count($transactions);
                        $data = $this->arrayToCsv($transactions);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Table transactions tidak tersedia'
                        ], 400);
                    }
                    break;

                case 'full':
                default:
                    // Backup semua table yang tersedia
                    $allData = [];
                    $tablesToBackup = ['settings']; // Selalu backup settings

                    // Tambahkan table lain jika ada
                    if (in_array('customers', $availableTables))
                        $tablesToBackup[] = 'customers';
                    if (in_array('orders', $availableTables))
                        $tablesToBackup[] = 'orders';
                    if (in_array('transactions', $availableTables))
                        $tablesToBackup[] = 'transactions';

                    foreach ($tablesToBackup as $table) {
                        $tableData = DB::select("SELECT * FROM {$table}");
                        $allData[$table] = $tableData;
                        $recordCount += count($tableData);
                    }
                    $data = $this->multiTableToCsv($allData);
                    break;
            }

            // Simpan file jika ada data
            if (!empty($data)) {
                Storage::put($filePath, $data);
                $fileSize = Storage::size($filePath) / 1024 / 1024; // Convert to MB

                // Save backup history
                DB::table('backup_histories')->insert([
                    'filename' => $filename,
                    'file_path' => $filePath,
                    'record_count' => $recordCount,
                    'backup_type' => $backupType,
                    'file_size_mb' => round($fileSize, 2),
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // PERBAIKAN: Return informasi file path yang lengkap
                $fullPath = storage_path('app/' . $filePath);
                $downloadUrl = route('backup.download', ['filename' => $filename]);

                return response()->json([
                    'success' => true,
                    'message' => 'Backup berhasil dilakukan',
                    'filename' => $filename,
                    'record_count' => $recordCount,
                    'file_size' => round($fileSize, 2),
                    'backup_type' => $backupType,
                    'file_path' => $filePath,
                    'full_path' => $fullPath,
                    'download_url' => $downloadUrl,
                    'tables_backed_up' => $tablesToBackup ?? [$backupType],
                    'storage_disk' => config('filesystems.default')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data untuk di-backup'
                ], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Backup error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan backup: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper function untuk mendapatkan table yang tersedia
    private function getAvailableTables()
    {
        $tables = [];

        try {
            // Cek table customers
            if (Schema::hasTable('customers')) {
                $tables[] = 'customers';
            }

            // Cek table orders
            if (Schema::hasTable('orders')) {
                $tables[] = 'orders';
            }

            // Cek table transactions
            if (Schema::hasTable('transactions')) {
                $tables[] = 'transactions';
            }

            // Table settings selalu ada
            $tables[] = 'settings';

        } catch (\Exception $e) {
            \Log::error('Error checking tables: ' . $e->getMessage());
        }

        return $tables;
    }

    // Reset data (dangerous operation)
    // Reset data - IMPROVED VERSION
    // Reset data - FIXED VERSION (tanpa transaction)
    public function resetData(Request $request)
    {
        $confirmation = $request->input('confirmation');

        if ($confirmation !== 'HAPUS-SEMUA-DATA') {
            return response()->json([
                'success' => false,
                'message' => 'Konfirmasi tidak valid'
            ], 400);
        }

        try {
            // Buat backup otomatis sebelum reset
            $backupFilename = 'pre_reset_backup_' . date('Y-m-d_H-i-s') . '.csv';
            $backupPath = 'backups/' . $backupFilename;

            // Backup data yang akan direset
            $tablesToReset = [];
            $backupData = [];

            // Cek dan backup table yang ada
            if (Schema::hasTable('customers')) {
                $customers = DB::select("SELECT * FROM customers");
                $tablesToReset[] = 'customers';
                $backupData['customers'] = $customers;
            }

            if (Schema::hasTable('orders')) {
                $orders = DB::select("SELECT * FROM orders");
                $tablesToReset[] = 'orders';
                $backupData['orders'] = $orders;
            }

            if (Schema::hasTable('transactions')) {
                $transactions = DB::select("SELECT * FROM transactions");
                $tablesToReset[] = 'transactions';
                $backupData['transactions'] = $transactions;
            }

            // Simpan backup
            if (!empty($backupData)) {
                $csvData = $this->multiTableToCsv($backupData);
                Storage::put($backupPath, $csvData);
            }

            // Reset hanya table yang ada (TANPA TRANSACTION)
            $resetTables = [];
            $resetCount = 0;

            if (in_array('transactions', $tablesToReset)) {
                $countBefore = DB::select("SELECT COUNT(*) as count FROM transactions")[0]->count;
                DB::statement("DELETE FROM transactions");
                if (DB::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
                    DB::statement("ALTER TABLE transactions AUTO_INCREMENT = 1");
                }
                $resetTables[] = 'transactions';
                $resetCount += $countBefore;
            }

            if (in_array('orders', $tablesToReset)) {
                $countBefore = DB::select("SELECT COUNT(*) as count FROM orders")[0]->count;
                DB::statement("DELETE FROM orders");
                if (DB::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
                    DB::statement("ALTER TABLE orders AUTO_INCREMENT = 1");
                }
                $resetTables[] = 'orders';
                $resetCount += $countBefore;
            }

            if (in_array('customers', $tablesToReset)) {
                $countBefore = DB::select("SELECT COUNT(*) as count FROM customers")[0]->count;
                DB::statement("DELETE FROM customers");
                if (DB::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
                    DB::statement("ALTER TABLE customers AUTO_INCREMENT = 1");
                }
                $resetTables[] = 'customers';
                $resetCount += $countBefore;
            }

            // Log reset activity
            \Log::info('Data reset performed', [
                'tables_reset' => $resetTables,
                'backup_file' => $backupFilename,
                'user_id' => auth()->id(),
                'reset_count' => $resetCount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil direset',
                'tables_reset' => $resetTables,
                'backup_file' => $backupFilename,
                'records_reset' => $resetCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Reset data error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mereset data: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper function to cast values based on type
    private function castValue($value, $type)
    {
        if ($value === null)
            return null;

        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    // Helper function to convert array to CSV
    private function arrayToCsv($data)
    {
        if (empty($data))
            return '';

        $output = fopen('php://temp', 'r+');

        // Write headers
        fputcsv($output, array_keys((array) $data[0]));

        // Write data
        foreach ($data as $row) {
            fputcsv($output, (array) $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    // Helper function for multi-table backup
    private function multiTableToCsv($tablesData)
    {
        $output = '';

        foreach ($tablesData as $tableName => $data) {
            if (!empty($data)) {
                $output .= "=== TABLE: {$tableName} ===\n";
                $output .= $this->arrayToCsv($data);
                $output .= "\n\n";
            }
        }

        return $output;
    }
}