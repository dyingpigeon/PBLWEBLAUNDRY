<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\ServiceType;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('transaction_items')->truncate();
        DB::table('transactions')->truncate();
        DB::table('service_items')->truncate();
        DB::table('services')->truncate();
        DB::table('service_types')->truncate();
        DB::table('customers')->truncate();
        DB::table('users')->truncate();
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

        // Create service types (sesuai migration service_types)
        $expressType = ServiceType::create([
            'name' => 'Express',
            'icon' => 'fas fa-bolt',
            'sort_order' => 1,
            'active' => true
        ]);

        $nonExpressType = ServiceType::create([
            'name' => 'Non Express',
            'icon' => 'fas fa-clock',
            'sort_order' => 2,
            'active' => true
        ]);

        // Create sample services sesuai migration
        $cuciBiasa = Service::create([
            'name' => 'Cuci Biasa',
            'type' => 'kiloan',
            'description' => 'Layanan cuci biasa untuk pakaian sehari-hari',
            'icon' => 'fas fa-soap',
            'color' => 'blue-500',
            'active' => true
        ]);

        $cuciSetrika = Service::create([
            'name' => 'Cuci Setrika',
            'type' => 'kiloan',
            'description' => 'Layanan cuci dan setrika lengkap',
            'icon' => 'fas fa-tshirt',
            'color' => 'green-500',
            'active' => true
        ]);

        $setrikaSaja = Service::create([
            'name' => 'Setrika Saja',
            'type' => 'kiloan',
            'description' => 'Layanan setrika saja untuk pakaian yang sudah dicuci',
            'icon' => 'fas fa-fire',
            'color' => 'orange-500',
            'active' => true
        ]);

        $laundrySatuan = Service::create([
            'name' => 'Laundry Satuan',
            'type' => 'satuan',
            'description' => 'Layanan laundry per item dengan penanganan khusus',
            'icon' => 'fas fa-tshirt',
            'color' => 'purple-500',
            'active' => true
        ]);

        // Create service items sesuai migration (tanpa category_id)
        // Service Kiloan - Cuci Biasa
        ServiceItem::create([
            'service_id' => $cuciBiasa->id,
            'name' => 'Pakaian Biasa', 
            'price' => 5000,
            'unit' => 'kg',
            'estimation_time' => 24,
            'description' => 'Layanan cuci biasa untuk pakaian sehari-hari'
        ]);

        // Service Kiloan - Cuci Setrika
        ServiceItem::create([
            'service_id' => $cuciSetrika->id,
            'name' => 'Pakaian Cuci Setrika',
            'price' => 8000,
            'unit' => 'kg',
            'estimation_time' => 24,
            'description' => 'Layanan cuci dan setrika lengkap'
        ]);

        // Service Kiloan - Setrika Saja
        ServiceItem::create([
            'service_id' => $setrikaSaja->id,
            'name' => 'Pakaian Setrika Saja',
            'price' => 4000,
            'unit' => 'kg',
            'estimation_time' => 24,
            'description' => 'Layanan setrika saja'
        ]);

        // Service Satuan - berbagai jenis item
        $satuanItems = [
            // Pakaian
            ['service_id' => $laundrySatuan->id, 'name' => 'Kemeja', 'price' => 10000, 'unit' => 'pcs', 'estimation_time' => 24],
            ['service_id' => $laundrySatuan->id, 'name' => 'Celana Panjang', 'price' => 12000, 'unit' => 'pcs', 'estimation_time' => 24],
            ['service_id' => $laundrySatuan->id, 'name' => 'Jaket', 'price' => 15000, 'unit' => 'pcs', 'estimation_time' => 24],
            ['service_id' => $laundrySatuan->id, 'name' => 'Kaos', 'price' => 8000, 'unit' => 'pcs', 'estimation_time' => 24],
            ['service_id' => $laundrySatuan->id, 'name' => 'Celana Pendek', 'price' => 7000, 'unit' => 'pcs', 'estimation_time' => 24],
            // Sepatu
            ['service_id' => $laundrySatuan->id, 'name' => 'Sepatu Sneakers', 'price' => 25000, 'unit' => 'pcs', 'estimation_time' => 48],
            ['service_id' => $laundrySatuan->id, 'name' => 'Sepatu Kulit', 'price' => 35000, 'unit' => 'pcs', 'estimation_time' => 48],
            // Sprei & Selimut
            ['service_id' => $laundrySatuan->id, 'name' => 'Sprei Single', 'price' => 20000, 'unit' => 'pcs', 'estimation_time' => 36],
            ['service_id' => $laundrySatuan->id, 'name' => 'Sprei Double', 'price' => 25000, 'unit' => 'pcs', 'estimation_time' => 36],
            ['service_id' => $laundrySatuan->id, 'name' => 'Selimut', 'price' => 30000, 'unit' => 'pcs', 'estimation_time' => 36],
            // Boneka
            ['service_id' => $laundrySatuan->id, 'name' => 'Boneka Kecil', 'price' => 15000, 'unit' => 'pcs', 'estimation_time' => 36],
            ['service_id' => $laundrySatuan->id, 'name' => 'Boneka Besar', 'price' => 25000, 'unit' => 'pcs', 'estimation_time' => 48],
            // Jas
            ['service_id' => $laundrySatuan->id, 'name' => 'Jas Formal', 'price' => 45000, 'unit' => 'pcs', 'estimation_time' => 48],
        ];

        foreach ($satuanItems as $item) {
            ServiceItem::create($item);
        }

        // Sample Transactions dengan struktur baru
        $currentDate = Carbon::now()->format('Ymd');
        
        // Transaction 1: Kiloan - Cuci Setrika
        $transaction1 = Transaction::create([
            'transaction_number' => 'TRX-' . $currentDate . '-0001',
            'customer_id' => $customer1->id,
            'order_type' => 'kiloan',
            'service_id' => $cuciSetrika->id,
            'total_amount' => 40000,
            'paid_amount' => 50000,
            'change_amount' => 10000,
            'weight' => 5.0, // 5 kg
            'payment_type' => 'now',
            'status' => 'process',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'notes' => 'Ada noda di bagian lengan',
            'order_date' => now()->subHours(3),
            'estimated_completion' => now()->addHours(5),
        ]);

        // Transaction 2: Kiloan - Setrika Saja
        $transaction2 = Transaction::create([
            'transaction_number' => 'TRX-' . $currentDate . '-0002',
            'customer_id' => $customer2->id,
            'order_type' => 'kiloan',
            'service_id' => $setrikaSaja->id,
            'total_amount' => 20000,
            'paid_amount' => 0,
            'change_amount' => 0,
            'weight' => 5.0, // 5 kg
            'payment_type' => 'later',
            'status' => 'new',
            'payment_status' => 'pending',
            'payment_method' => null,
            'notes' => 'Semua pakaian warna putih',
            'order_date' => now()->subHours(1),
            'estimated_completion' => now()->addHours(3),
        ]);

        // Transaction 3: Satuan - Mix items
        $transaction3 = Transaction::create([
            'transaction_number' => 'TRX-' . $currentDate . '-0003',
            'customer_id' => $customer3->id,
            'order_type' => 'satuan',
            'service_id' => $laundrySatuan->id,
            'total_amount' => 87000,
            'paid_amount' => 50000,
            'change_amount' => 0,
            'weight' => null, // tidak ada weight untuk satuan
            'payment_type' => 'now',
            'status' => 'new',
            'payment_status' => 'partial',
            'payment_method' => 'transfer',
            'notes' => 'Jaket kulit, hati-hati',
            'order_date' => now()->subHours(2),
            'estimated_completion' => now()->addHours(6),
        ]);

        // Transaction 4: Satuan - Pakaian saja
        $transaction4 = Transaction::create([
            'transaction_number' => 'TRX-' . $currentDate . '-0004',
            'customer_id' => $customer4->id,
            'order_type' => 'satuan',
            'service_id' => $laundrySatuan->id,
            'total_amount' => 36000,
            'paid_amount' => 36000,
            'change_amount' => 0,
            'weight' => null,
            'payment_type' => 'now',
            'status' => 'ready',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'notes' => 'Pakaian bayi, gunakan deterjen khusus',
            'order_date' => now()->subDays(1),
            'estimated_completion' => now()->subHours(2),
        ]);

        // Transaction Items
        // Transaction 1 Items (Kiloan)
        TransactionItem::create([
            'transaction_id' => $transaction1->id,
            'service_item_id' => 2, // Pakaian Cuci Setrika
            'item_name' => 'Pakaian Cuci Setrika',
            'quantity' => 5.0,
            'unit_price' => 8000,
            'subtotal' => 40000,
            'unit' => 'kg',
            'notes' => 'Baju dan celana campur'
        ]);

        // Transaction 2 Items (Kiloan)
        TransactionItem::create([
            'transaction_id' => $transaction2->id,
            'service_item_id' => 3, // Pakaian Setrika Saja
            'item_name' => 'Pakaian Setrika Saja',
            'quantity' => 5.0,
            'unit_price' => 4000,
            'subtotal' => 20000,
            'unit' => 'kg',
            'notes' => 'Pakaian sudah dicuci'
        ]);

        // Transaction 3 Items (Satuan - Mix)
        TransactionItem::create([
            'transaction_id' => $transaction3->id,
            'service_item_id' => 4, // Kemeja
            'item_name' => 'Kemeja',
            'quantity' => 2,
            'unit_price' => 10000,
            'subtotal' => 20000,
            'unit' => 'pcs',
            'notes' => 'Kemeja kerja'
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction3->id,
            'service_item_id' => 5, // Celana Panjang
            'item_name' => 'Celana Panjang',
            'quantity' => 1,
            'unit_price' => 12000,
            'subtotal' => 12000,
            'unit' => 'pcs',
            'notes' => 'Celana bahan'
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction3->id,
            'service_item_id' => 9, // Sepatu Sneakers
            'item_name' => 'Sepatu Sneakers',
            'quantity' => 1,
            'unit_price' => 25000,
            'subtotal' => 25000,
            'unit' => 'pcs',
            'notes' => 'Sepatu putih'
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction3->id,
            'service_item_id' => 16, // Jas Formal
            'item_name' => 'Jas Formal',
            'quantity' => 1,
            'unit_price' => 30000,
            'subtotal' => 30000,
            'unit' => 'pcs',
            'notes' => 'Jas warna hitam'
        ]);

        // Transaction 4 Items (Satuan - Pakaian)
        TransactionItem::create([
            'transaction_id' => $transaction4->id,
            'service_item_id' => 4, // Kemeja
            'item_name' => 'Kemeja',
            'quantity' => 2,
            'unit_price' => 10000,
            'subtotal' => 20000,
            'unit' => 'pcs',
            'notes' => 'Kemeja bayi'
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction4->id,
            'service_item_id' => 7, // Kaos
            'item_name' => 'Kaos',
            'quantity' => 2,
            'unit_price' => 8000,
            'subtotal' => 16000,
            'unit' => 'pcs',
            'notes' => 'Kaos bayi'
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin Login: admin@laundryku.com / password');
    }
}