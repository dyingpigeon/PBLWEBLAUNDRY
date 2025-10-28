<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\Transaction;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('transaction_items')->truncate();
        DB::table('transactions')->truncate();
        DB::table('notifications')->truncate();
        DB::table('reports')->truncate();
        DB::table('customers')->truncate();
        DB::table('service_items')->truncate();
        DB::table('services')->truncate();
        DB::table('users')->truncate();
        DB::table('business_hours')->truncate();
        DB::table('settings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create admin user
        $admin = User::create([
            'name' => 'Admin Laundry',
            'email' => 'admin@laundryku.com',
            'password' => bcrypt('password'),
        ]);

        // Create sample customers
        $customer1 = Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 123'
        ]);

        $customer2 = Customer::create([
            'name' => 'Siti Rahayu',
            'phone' => '081234567891',
            'address' => 'Jl. Sudirman No. 45'
        ]);

        $customer3 = Customer::create([
            'name' => 'Ahmad Fauzi',
            'phone' => '081234567892',
            'address' => 'Jl. Thamrin No. 67'
        ]);

        $customer4 = Customer::create([
            'name' => 'Dewi Lestari',
            'phone' => '081234567893',
            'address' => 'Jl. Gatot Subroto No. 89'
        ]);

        // Create sample services
        $cuciBiasa = Service::create([
            'name' => 'Cuci Biasa',
            'category' => 'Cuci',
            'icon' => 'fas fa-soap',
            'color' => 'blue-500'
        ]);

        $cuciSetrika = Service::create([
            'name' => 'Cuci Setrika',
            'category' => 'Cuci',
            'icon' => 'fas fa-tshirt',
            'color' => 'green-500'
        ]);

        $setrikaSaja = Service::create([
            'name' => 'Setrika Saja',
            'category' => 'Setrika',
            'icon' => 'fas fa-fire',
            'color' => 'orange-500'
        ]);

        $dryClean = Service::create([
            'name' => 'Dry Clean',
            'category' => 'Khusus',
            'icon' => 'fas fa-gem',
            'color' => 'purple-500'
        ]);

        // Create service items
        ServiceItem::create(['service_id' => $cuciBiasa->id, 'name' => 'Baju', 'price' => 5000]);
        ServiceItem::create(['service_id' => $cuciBiasa->id, 'name' => 'Celana', 'price' => 6000]);
        ServiceItem::create(['service_id' => $cuciBiasa->id, 'name' => 'Jaket', 'price' => 10000]);
        ServiceItem::create(['service_id' => $cuciSetrika->id, 'name' => 'Baju', 'price' => 8000]);
        ServiceItem::create(['service_id' => $cuciSetrika->id, 'name' => 'Celana', 'price' => 9000]);
        ServiceItem::create(['service_id' => $cuciSetrika->id, 'name' => 'Jaket', 'price' => 15000]);
        ServiceItem::create(['service_id' => $setrikaSaja->id, 'name' => 'Baju', 'price' => 4000]);
        ServiceItem::create(['service_id' => $setrikaSaja->id, 'name' => 'Celana', 'price' => 5000]);
        ServiceItem::create(['service_id' => $setrikaSaja->id, 'name' => 'Jaket', 'price' => 8000]);
        ServiceItem::create(['service_id' => $dryClean->id, 'name' => 'Baju', 'price' => 15000]);
        ServiceItem::create(['service_id' => $dryClean->id, 'name' => 'Celana', 'price' => 18000]);
        ServiceItem::create(['service_id' => $dryClean->id, 'name' => 'Jaket', 'price' => 25000]);

        // Business Hours
        $days = [
            ['day' => 'monday', 'open_time' => '08:00:00', 'close_time' => '20:00:00', 'is_closed' => false],
            ['day' => 'tuesday', 'open_time' => '08:00:00', 'close_time' => '20:00:00', 'is_closed' => false],
            ['day' => 'wednesday', 'open_time' => '08:00:00', 'close_time' => '20:00:00', 'is_closed' => false],
            ['day' => 'thursday', 'open_time' => '08:00:00', 'close_time' => '20:00:00', 'is_closed' => false],
            ['day' => 'friday', 'open_time' => '08:00:00', 'close_time' => '20:00:00', 'is_closed' => false],
            ['day' => 'saturday', 'open_time' => '08:00:00', 'close_time' => '18:00:00', 'is_closed' => false],
            ['day' => 'sunday', 'open_time' => '08:00:00', 'close_time' => '16:00:00', 'is_closed' => false],
        ];

        foreach ($days as $day) {
            DB::table('business_hours')->insert(array_merge($day, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Settings
        $settings = [
            ['key' => 'business_name', 'value' => 'LaundryKu', 'type' => 'string', 'group' => 'business', 'description' => 'Nama bisnis laundry'],
            ['key' => 'business_address', 'value' => 'Jl. Contoh No. 123, Jakarta', 'type' => 'string', 'group' => 'business', 'description' => 'Alamat lengkap laundry'],
            ['key' => 'business_phone', 'value' => '081234567890', 'type' => 'string', 'group' => 'business', 'description' => 'Nomor telepon laundry'],
            ['key' => 'business_email', 'value' => 'info@laundryku.com', 'type' => 'string', 'group' => 'business', 'description' => 'Email laundry'],
            ['key' => 'receipt_header', 'value' => "LAUNDRYKU\nJl. Contoh No. 123, Jakarta\nTelp: 081234567890", 'type' => 'string', 'group' => 'receipt', 'description' => 'Header struk/nota'],
            ['key' => 'receipt_footer', 'value' => "Terima kasih atas kunjungan Anda\n*** Barang yang sudah dicuci tidak dapat ditukar ***", 'type' => 'string', 'group' => 'receipt', 'description' => 'Footer struk/nota'],
            ['key' => 'show_logo_on_receipt', 'value' => 'true', 'type' => 'boolean', 'group' => 'receipt', 'description' => 'Tampilkan logo di struk'],
            ['key' => 'auto_print_receipt', 'value' => 'false', 'type' => 'boolean', 'group' => 'receipt', 'description' => 'Print otomatis setelah transaksi'],
            ['key' => 'notification_new_order', 'value' => 'true', 'type' => 'boolean', 'group' => 'notification', 'description' => 'Notifikasi pesanan baru'],
            ['key' => 'notification_status_update', 'value' => 'true', 'type' => 'boolean', 'group' => 'notification', 'description' => 'Notifikasi perubahan status'],
            ['key' => 'notification_reminder', 'value' => 'true', 'type' => 'boolean', 'group' => 'notification', 'description' => 'Notifikasi pengingat'],
            ['key' => 'currency', 'value' => 'IDR', 'type' => 'string', 'group' => 'general', 'description' => 'Mata uang yang digunakan'],
            ['key' => 'timezone', 'value' => 'Asia/Jakarta', 'type' => 'string', 'group' => 'general', 'description' => 'Zona waktu'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Sample Transactions - PAKAI STATIC UNIQUE NUMBERS
        $transactions = [
            [
                'transaction_number' => 'LDR-1001',
                'customer_id' => $customer1->id,
                'service_id' => $cuciSetrika->id,
                'total_amount' => 40000,
                'paid_amount' => 50000,
                'change_amount' => 10000,
                'notes' => 'Ada noda di bagian lengan',
                'customer_notes' => 'Tolong noda di lengan kanan dibersihkan',
                'status' => 'washing',
                'payment_status' => 'overpaid',
                'payment_method' => 'cash',
                'timeline' => json_encode([
                    ['status' => 'new', 'time' => now()->subHours(3)->toISOString(), 'completed' => true],
                    ['status' => 'washing', 'time' => now()->subHours(1)->toISOString(), 'completed' => true],
                    ['status' => 'ironing', 'time' => null, 'completed' => false],
                    ['status' => 'ready', 'time' => null, 'completed' => false],
                    ['status' => 'picked_up', 'time' => null, 'completed' => false]
                ]),
                'order_date' => now()->subHours(3),
                'estimated_completion' => now()->addHours(2),
                'washing_started_at' => now()->subHours(1),
                'ironing_started_at' => null,
                'completed_at' => null,
                'picked_up_at' => null,
            ],
            [
                'transaction_number' => 'LDR-1002',
                'customer_id' => $customer2->id,
                'service_id' => $setrikaSaja->id,
                'total_amount' => 27000,
                'paid_amount' => 30000,
                'change_amount' => 3000,
                'notes' => 'Semua pakaian warna putih',
                'customer_notes' => 'Mohon disetrika dengan rapi',
                'status' => 'ready',
                'payment_status' => 'paid',
                'payment_method' => 'cash',
                'timeline' => json_encode([
                    ['status' => 'new', 'time' => now()->subDays(1)->toISOString(), 'completed' => true],
                    ['status' => 'washing', 'time' => null, 'completed' => false],
                    ['status' => 'ironing', 'time' => now()->subHours(4)->toISOString(), 'completed' => true],
                    ['status' => 'ready', 'time' => now()->subHours(2)->toISOString(), 'completed' => true],
                    ['status' => 'picked_up', 'time' => null, 'completed' => false]
                ]),
                'order_date' => now()->subDays(1),
                'estimated_completion' => now()->subHours(2),
                'washing_started_at' => null,
                'ironing_started_at' => now()->subHours(4),
                'completed_at' => now()->subHours(2),
                'picked_up_at' => null,
            ],
            [
                'transaction_number' => 'LDR-1003',
                'customer_id' => $customer3->id,
                'service_id' => $dryClean->id,
                'total_amount' => 75000,
                'paid_amount' => 0,
                'change_amount' => 0,
                'notes' => 'Jaket kulit, hati-hati',
                'customer_notes' => 'Ini jaket kulit import, mohon diperlakukan khusus',
                'status' => 'new',
                'payment_status' => 'pending',
                'payment_method' => 'cash',
                'timeline' => json_encode([
                    ['status' => 'new', 'time' => now()->subHours(1)->toISOString(), 'completed' => true],
                    ['status' => 'washing', 'time' => null, 'completed' => false],
                    ['status' => 'ironing', 'time' => null, 'completed' => false],
                    ['status' => 'ready', 'time' => null, 'completed' => false],
                    ['status' => 'picked_up', 'time' => null, 'completed' => false]
                ]),
                'order_date' => now()->subHours(1),
                'estimated_completion' => now()->addHours(24),
                'washing_started_at' => null,
                'ironing_started_at' => null,
                'completed_at' => null,
                'picked_up_at' => null,
            ],
            [
                'transaction_number' => 'LDR-1004',
                'customer_id' => $customer4->id,
                'service_id' => $cuciBiasa->id,
                'total_amount' => 32000,
                'paid_amount' => 32000,
                'change_amount' => 0,
                'notes' => 'Pakaian bayi, gunakan deterjen khusus',
                'customer_notes' => 'Ini pakaian bayi, mohon gunakan deterjen hipoalergenik',
                'status' => 'picked_up',
                'payment_status' => 'paid',
                'payment_method' => 'transfer',
                'timeline' => json_encode([
                    ['status' => 'new', 'time' => now()->subDays(2)->toISOString(), 'completed' => true],
                    ['status' => 'washing', 'time' => now()->subDays(1)->toISOString(), 'completed' => true],
                    ['status' => 'ironing', 'time' => null, 'completed' => false],
                    ['status' => 'ready', 'time' => now()->subHours(6)->toISOString(), 'completed' => true],
                    ['status' => 'picked_up', 'time' => now()->subHours(2)->toISOString(), 'completed' => true]
                ]),
                'order_date' => now()->subDays(2),
                'estimated_completion' => now()->subHours(6),
                'washing_started_at' => now()->subDays(1),
                'ironing_started_at' => null,
                'completed_at' => now()->subHours(6),
                'picked_up_at' => now()->subHours(2),
            ],
        ];

        // HANYA SATU KALI CREATE TRANSACTIONS!
        foreach ($transactions as $transactionData) {
            $transaction = Transaction::create($transactionData);

            // Add transaction items based on transaction number - PAKAI LDR-1001, LDR-1002, etc
            switch ($transactionData['transaction_number']) {
                case 'LDR-1001':
                    DB::table('transaction_items')->insert([
                        [
                            'transaction_id' => $transaction->id,
                            'service_item_id' => 4, // Baju Cuci Setrika
                            'item_name' => 'Baju',
                            'quantity' => 2,
                            'unit_price' => 8000,
                            'subtotal' => 16000,
                            'notes' => 'Baju kemeja',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'transaction_id' => $transaction->id,
                            'service_item_id' => 5, // Celana Cuci Setrika
                            'item_name' => 'Celana',
                            'quantity' => 2,
                            'unit_price' => 9000,
                            'subtotal' => 18000,
                            'notes' => 'Celana panjang',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    ]);
                    break;

                case 'LDR-1002':
                    DB::table('transaction_items')->insert([
                        [
                            'transaction_id' => $transaction->id,
                            'service_item_id' => 7, // Baju Setrika Saja
                            'item_name' => 'Baju',
                            'quantity' => 3,
                            'unit_price' => 4000,
                            'subtotal' => 12000,
                            'notes' => 'Baju kaos',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'transaction_id' => $transaction->id,
                            'service_item_id' => 8, // Celana Setrika Saja
                            'item_name' => 'Celana',
                            'quantity' => 3,
                            'unit_price' => 5000,
                            'subtotal' => 15000,
                            'notes' => 'Celana jeans',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    ]);
                    break;

                case 'LDR-1003':
                    DB::table('transaction_items')->insert([
                        [
                            'transaction_id' => $transaction->id,
                            'service_item_id' => 12, // Jaket Dry Clean
                            'item_name' => 'Jaket',
                            'quantity' => 1,
                            'unit_price' => 25000,
                            'subtotal' => 25000,
                            'notes' => 'Jaket kulit, hati-hati',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'transaction_id' => $transaction->id,
                            'service_item_id' => 10, // Baju Dry Clean
                            'item_name' => 'Baju',
                            'quantity' => 2,
                            'unit_price' => 15000,
                            'subtotal' => 30000,
                            'notes' => 'Baju sutra',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'transaction_id' => $transaction->id,
                            'service_item_id' => 11, // Celana Dry Clean
                            'item_name' => 'Celana',
                            'quantity' => 1,
                            'unit_price' => 18000,
                            'subtotal' => 18000,
                            'notes' => 'Celana bahan premium',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    ]);
                    break;

                case 'LDR-1004':
                    DB::table('transaction_items')->insert([
                        [
                            'transaction_id' => $transaction->id,
                            'service_item_id' => 1, // Baju Cuci Biasa
                            'item_name' => 'Baju',
                            'quantity' => 4,
                            'unit_price' => 5000,
                            'subtotal' => 20000,
                            'notes' => 'Baju bayi',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'transaction_id' => $transaction->id,
                            'service_item_id' => 2, // Celana Cuci Biasa
                            'item_name' => 'Celana',
                            'quantity' => 2,
                            'unit_price' => 6000,
                            'subtotal' => 12000,
                            'notes' => 'Celana bayi',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    ]);
                    break;
            }
        }

        // Sample Notifications - UPDATE RELATED_ID DENGAN BENAR
        $notifications = [
            [
                'type' => 'new_order',
                'title' => 'Pesanan Baru',
                'message' => 'Budi Santoso membuat pesanan cuci setrika 2 kg',
                'data' => json_encode(['order_id' => 1, 'customer_name' => 'Budi Santoso', 'service' => 'Cuci Setrika']),
                'read' => false,
                'user_id' => $admin->id,
                'related_id' => 1, // ID transaction LDR-1001
                'related_type' => 'App\Models\Transaction',
                'created_at' => now()->subHours(3),
                'updated_at' => now(),
            ],
            [
                'type' => 'status_update',
                'title' => 'Status Berubah',
                'message' => 'Pesanan #LDR-1001 sedang dicuci',
                'data' => json_encode(['order_id' => 1, 'status' => 'washing', 'previous_status' => 'new']),
                'read' => false,
                'user_id' => $admin->id,
                'related_id' => 1,
                'related_type' => 'App\Models\Transaction',
                'created_at' => now()->subHours(1),
                'updated_at' => now(),
            ],
            [
                'type' => 'reminder',
                'title' => 'Pengingat',
                'message' => 'Pesanan #LDR-1002 sudah selesai dan siap diambil',
                'data' => json_encode(['order_id' => 2, 'status' => 'ready', 'customer_phone' => '081234567891']),
                'read' => true,
                'user_id' => $admin->id,
                'related_id' => 2,
                'related_type' => 'App\Models\Transaction',
                'created_at' => now()->subHours(2),
                'updated_at' => now(),
            ],
        ];

        foreach ($notifications as $notification) {
            DB::table('notifications')->insert($notification);
        }

        // Sample Reports
        DB::table('reports')->insert([
            'type' => 'weekly',
            'start_date' => now()->startOfWeek(),
            'end_date' => now()->endOfWeek(),
            'data' => json_encode([
                'daily_income' => [450000, 520000, 380000, 610000, 550000, 480000, 590000],
                'services_distribution' => [
                    'Cuci Setrika' => 45,
                    'Cuci Biasa' => 32,
                    'Setrika Saja' => 28,
                    'Dry Clean' => 19
                ],
                'top_customers' => [
                    ['name' => 'Budi Santoso', 'total_orders' => 12, 'total_spent' => 480000],
                    ['name' => 'Siti Rahayu', 'total_orders' => 8, 'total_spent' => 320000],
                ]
            ]),
            'total_income' => 3180000,
            'total_orders' => 124,
            'user_id' => $admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}