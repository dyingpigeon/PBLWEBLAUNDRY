<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index()
    {
        // Query untuk mendapatkan semua services yang aktif beserta item-nya
        $query = "
            SELECT 
                s.id,
                s.name,
                s.category,
                s.description,
                s.icon,
                s.color,
                s.active,
                si.id as item_id,
                si.name as item_name,
                si.price,
                si.unit,
                si.description as item_description
            FROM services s
            LEFT JOIN service_items si ON s.id = si.service_id AND si.active = 1
            WHERE s.active = 1
            ORDER BY s.name, si.name
        ";

        $services = DB::select($query);
        
        // Hitung total services aktif
        $countQuery = "SELECT COUNT(*) as total FROM services WHERE active = 1";
        $totalResult = DB::select($countQuery);
        $totalServices = $totalResult[0]->total;

        // Format data untuk view
        $formattedServices = [];
        foreach ($services as $service) {
            $serviceId = $service->id;
            
            if (!isset($formattedServices[$serviceId])) {
                $formattedServices[$serviceId] = [
                    'id' => $service->id,
                    'name' => $service->name,
                    'category' => $service->category,
                    'description' => $service->description,
                    'icon' => $service->icon,
                    'color' => 'bg-' . $service->color,
                    'active' => (bool)$service->active,
                    'items' => []
                ];
            }

            // Tambahkan item jika ada
            if ($service->item_id) {
                $formattedServices[$serviceId]['items'][] = [
                    'id' => $service->item_id,
                    'name' => $service->item_name,
                    'price' => (float)$service->price,
                    'unit' => $service->unit,
                    'description' => $service->item_description
                ];
            }
        }

        // Konversi ke array indexed
        $servicesData = array_values($formattedServices);

        return view('services.index', compact('totalServices', 'servicesData'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'icon' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.price' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();

        try {
            // Insert service dengan parameter binding
            $serviceQuery = "
                INSERT INTO services (name, category, icon, color, created_at, updated_at)
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ";

            DB::insert($serviceQuery, [
                $validated['name'],
                $validated['category'],
                $validated['icon'],
                str_replace('bg-', '', $validated['color'])
            ]);

            $serviceId = DB::getPdo()->lastInsertId();

            // Insert service items
            foreach ($validated['items'] as $item) {
                $itemQuery = "
                    INSERT INTO service_items (service_id, name, price, unit, created_at, updated_at)
                    VALUES (?, ?, ?, 'kg', NOW(), NOW())
                ";

                DB::insert($itemQuery, [
                    $serviceId,
                    $item['name'],
                    $item['price']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Service berhasil ditambahkan',
                'service_id' => $serviceId
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan service: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleService(Request $request, $id)
    {
        $validated = $request->validate([
            'active' => 'required|boolean'
        ]);

        $query = "UPDATE services SET active = ?, updated_at = NOW() WHERE id = ?";
        $affected = DB::update($query, [$validated['active'], $id]);

        if ($affected === 0) {
            return response()->json(['success' => false, 'message' => 'Service tidak ditemukan']);
        }

        // Juga update service items terkait
        $updateItemsQuery = "UPDATE service_items SET active = ? WHERE service_id = ?";
        DB::update($updateItemsQuery, [$validated['active'], $id]);

        return response()->json(['success' => true, 'message' => 'Status service berhasil diupdate']);
    }

    public function updateServiceItem(Request $request, $serviceId, $itemId)
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'name' => 'required|string|max:255'
        ]);

        // Cek apakah service item exists
        $checkQuery = "SELECT id FROM service_items WHERE id = ? AND service_id = ?";
        $item = DB::select($checkQuery, [$itemId, $serviceId]);

        if (empty($item)) {
            return response()->json(['success' => false, 'message' => 'Item service tidak ditemukan']);
        }

        // Update price
        $updateQuery = "UPDATE service_items SET price = ?, name = ?, updated_at = NOW() WHERE id = ?";
        $affected = DB::update($updateQuery, [
            $validated['price'],
            $validated['name'],
            $itemId
        ]);

        return response()->json(['success' => true, 'message' => 'Harga berhasil diupdate']);
    }

    // FITUR BARU: Menambah item ke service yang sudah ada
    public function addServiceItem(Request $request, $serviceId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'unit' => 'sometimes|string|max:20',
            'description' => 'sometimes|string|max:500'
        ]);

        DB::beginTransaction();

        try {
            // Cek apakah service exists dan aktif
            $serviceCheckQuery = "SELECT id FROM services WHERE id = ? AND active = 1";
            $service = DB::select($serviceCheckQuery, [$serviceId]);

            if (empty($service)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service tidak ditemukan atau tidak aktif'
                ], 404);
            }

            // Cek apakah item dengan nama yang sama sudah ada di service ini
            $duplicateCheckQuery = "SELECT id FROM service_items WHERE service_id = ? AND name = ? AND active = 1";
            $existingItem = DB::select($duplicateCheckQuery, [
                $serviceId,
                $validated['name']
            ]);

            if (!empty($existingItem)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item dengan nama yang sama sudah ada dalam service ini'
                ], 422);
            }

            // Insert new service item
            $itemQuery = "
                INSERT INTO service_items (service_id, name, price, unit, description, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ";

            DB::insert($itemQuery, [
                $serviceId,
                $validated['name'],
                $validated['price'],
                $validated['unit'] ?? 'kg',
                $validated['description'] ?? null
            ]);

            $itemId = DB::getPdo()->lastInsertId();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil ditambahkan ke service',
                'item_id' => $itemId
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan item: ' . $e->getMessage()
            ], 500);
        }
    }

    // FITUR BARU: Menghapus item individual (soft delete)
    public function deleteServiceItem(Request $request, $serviceId, $itemId)
    {
        DB::beginTransaction();

        try {
            // Cek apakah service item exists dan milik service yang benar
            $checkQuery = "SELECT id FROM service_items WHERE id = ? AND service_id = ? AND active = 1";
            $item = DB::select($checkQuery, [$itemId, $serviceId]);

            if (empty($item)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item service tidak ditemukan'
                ], 404);
            }

            // Soft delete: set active = 0
            $deleteQuery = "UPDATE service_items SET active = 0, updated_at = NOW() WHERE id = ?";
            $affected = DB::update($deleteQuery, [$itemId]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus item: ' . $e->getMessage()
            ], 500);
        }
    }

    // FITUR BARU: Get detail service dengan items (untuk edit form)
    public function getServiceWithItems($serviceId)
    {
        try {
            // Get service details
            $serviceQuery = "SELECT * FROM services WHERE id = ? AND active = 1";
            $service = DB::select($serviceQuery, [$serviceId]);

            if (empty($service)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service tidak ditemukan'
                ], 404);
            }

            // Get service items
            $itemsQuery = "
                SELECT id, name, price, unit, description 
                FROM service_items 
                WHERE service_id = ? AND active = 1 
                ORDER BY name
            ";
            $items = DB::select($itemsQuery, [$serviceId]);

            return response()->json([
                'success' => true,
                'service' => $service[0],
                'items' => $items
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data service: ' . $e->getMessage()
            ], 500);
        }
    }
}