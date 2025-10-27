<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceItem;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        User::create([
            'name' => 'Admin Laundry',
            'email' => 'admin@laundryku.com',
            'password' => bcrypt('password'),
        ]);

        // Create sample customers
        Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 123'
        ]);

        Customer::create([
            'name' => 'Siti Rahayu', 
            'phone' => '081234567891',
            'address' => 'Jl. Sudirman No. 45'
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

        // Create service items
        ServiceItem::create([
            'service_id' => $cuciBiasa->id,
            'name' => 'Baju',
            'price' => 5000
        ]);

        ServiceItem::create([
            'service_id' => $cuciBiasa->id,
            'name' => 'Celana', 
            'price' => 6000
        ]);

        ServiceItem::create([
            'service_id' => $cuciSetrika->id,
            'name' => 'Baju',
            'price' => 8000
        ]);

        ServiceItem::create([
            'service_id' => $cuciSetrika->id,
            'name' => 'Celana',
            'price' => 9000 
        ]);

        ServiceItem::create([
            'service_id' => $setrikaSaja->id,
            'name' => 'Baju',
            'price' => 4000
        ]);

        ServiceItem::create([
            'service_id' => $setrikaSaja->id,
            'name' => 'Celana',
            'price' => 5000
        ]);
    }
}