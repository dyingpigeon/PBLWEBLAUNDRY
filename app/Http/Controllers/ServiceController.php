<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    public function index()
    {
        Log::debug('ServiceController@index: Memulai proses mengambil data services');

        try {
            // Query untuk mendapatkan semua services beserta item-nya (aktif & non-aktif)
            $query = "
                SELECT 
                    s.id,
                    s.name,
                    s.type,
                    s.category,
                    s.description,
                    s.icon,
                    s.color,
                    s.active,
                    si.id as item_id,
                    si.name as item_name,
                    si.price,
                    si.unit,
                    si.estimation_time,
                    si.description as item_description,
                    si.active as item_active,
                    sc.name as category_name,
                    sc.icon as category_icon
                FROM services s
                LEFT JOIN service_items si ON s.id = si.service_id AND si.active = 1
                LEFT JOIN service_categories sc ON si.category_id = sc.id
                ORDER BY s.active DESC, s.name, si.name
            ";

            Log::debug('ServiceController@index: Menjalankan query utama services');
            $startTime = microtime(true);
            $services = DB::select($query);
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::debug('ServiceController@index: Berhasil mengambil ' . count($services) . ' records services', [
                'execution_time_ms' => $executionTime
            ]);

            // Hitung total services
            $countQuery = "SELECT COUNT(*) as total FROM services";
            Log::debug('ServiceController@index: Menghitung total services');
            $totalResult = DB::select($countQuery);
            $totalServices = $totalResult[0]->total;
            Log::debug('ServiceController@index: Total services: ' . $totalServices);

            // Format data untuk view
            Log::debug('ServiceController@index: Memulai formatting data services');
            $formattedServices = [];
            $totalItems = 0;

            foreach ($services as $service) {
                $serviceId = $service->id;

                if (!isset($formattedServices[$serviceId])) {
                    $formattedServices[$serviceId] = [
                        'id' => $service->id,
                        'name' => $service->name,
                        'type' => $service->type,
                        'category' => $service->category,
                        'description' => $service->description,
                        'icon' => $service->icon,
                        'color' => 'bg-' . $service->color,
                        'active' => (bool) $service->active,
                        'items' => []
                    ];
                    Log::debug('ServiceController@index: Membuat service baru', [
                        'service_id' => $serviceId,
                        'service_name' => $service->name,
                        'active' => $service->active
                    ]);
                }

                // Tambahkan item jika ada
                if ($service->item_id) {
                    $formattedServices[$serviceId]['items'][] = [
                        'id' => $service->item_id,
                        'name' => $service->item_name,
                        'price' => (float) $service->price,
                        'unit' => $service->unit,
                        'estimation_time' => $service->estimation_time,
                        'description' => $service->item_description,
                        'active' => (bool) $service->item_active,
                        'category_name' => $service->category_name,
                        'category_icon' => $service->category_icon
                    ];
                    $totalItems++;
                }
            }

            // Konversi ke array indexed
            $servicesData = array_values($formattedServices);
            Log::debug('ServiceController@index: Formatting selesai', [
                'total_services_formatted' => count($servicesData),
                'total_items' => $totalItems,
                'active_services' => count(array_filter($servicesData, function ($service) {
                    return $service['active'];
                }))
            ]);

            // Get categories untuk filter
            $categoriesQuery = "SELECT DISTINCT category FROM services WHERE category IS NOT NULL ORDER BY category";
            Log::debug('ServiceController@index: Mengambil categories untuk filter');
            $categoriesResults = DB::select($categoriesQuery);
            $categories = array_column($categoriesResults, 'category');
            Log::debug('ServiceController@index: Berhasil mengambil categories', [
                'categories_count' => count($categories),
                'categories_list' => array_column($categories, 'category')
            ]);

            Log::debug('ServiceController@index: Proses selesai, mengembalikan view');
            return view('services.index', compact('totalServices', 'servicesData', 'categories'));

        } catch (\Exception $e) {
            Log::error('ServiceController@index: Error - ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'query_executed' => $query ?? 'N/A'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data services: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        Log::debug('ServiceController@show: Memulai proses mengambil detail service', ['service_id' => $id]);

        try {
            // Get service details dengan items aktif
            $serviceQuery = "
            SELECT 
                s.*,
                si.id as item_id,
                si.name as item_name,
                si.price,
                si.unit,
                si.estimation_time,
                si.description as item_description
            FROM services s
            LEFT JOIN service_items si ON s.id = si.service_id AND si.active = 1
            WHERE s.id = ?
            ORDER BY si.name
            ";

            Log::debug('ServiceController@show: Menjalankan query detail service');
            $startTime = microtime(true);
            $services = DB::select($serviceQuery, [$id]);
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::debug('ServiceController@show: Query selesai', [
                'results_count' => count($services),
                'execution_time_ms' => $executionTime
            ]);

            if (empty($services)) {
                Log::warning('ServiceController@show: Service tidak ditemukan', ['service_id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Service tidak ditemukan'
                ], 404);
            }

            // Format data
            Log::debug('ServiceController@show: Memulai formatting data service');
            $serviceData = [
                'id' => $services[0]->id,
                'name' => $services[0]->name,
                'type' => $services[0]->type,
                'category' => $services[0]->category,
                'description' => $services[0]->description,
                'icon' => $services[0]->icon,
                'color' => $services[0]->color,
                'active' => (bool) $services[0]->active,
                'items' => []
            ];

            $itemCount = 0;
            $totalItemPrice = 0;
            foreach ($services as $service) {
                if ($service->item_id) {
                    $serviceData['items'][] = [
                        'id' => $service->item_id,
                        'name' => $service->item_name,
                        'price' => (float) $service->price,
                        'unit' => $service->unit,
                        'estimation_time' => $service->estimation_time,
                        'description' => $service->item_description
                    ];
                    $itemCount++;
                    $totalItemPrice += (float) $service->price;
                }
            }

            Log::debug('ServiceController@show: Formatting selesai', [
                'service_name' => $serviceData['name'],
                'items_count' => $itemCount,
                'total_items_price' => $totalItemPrice,
                'service_active' => $serviceData['active']
            ]);

            Log::debug('ServiceController@show: Proses selesai, mengembalikan response JSON');
            return response()->json([
                'success' => true,
                'service' => $serviceData
            ]);

        } catch (\Exception $e) {
            Log::error('ServiceController@show: Error - ' . $e->getMessage(), [
                'service_id' => $id,
                'trace' => $e->getTraceAsString(),
                'query_executed' => $serviceQuery ?? 'N/A'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data service: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        Log::debug('ServiceController@store: Memulai proses membuat service baru');
        Log::debug('ServiceController@store: Data request received', [
            'name' => $request->name,
            'type' => $request->type,
            'category' => $request->category,
            'items_count' => count($request->items ?? [])
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:kiloan,satuan,khusus',
            'category' => 'required|string|max:100',
            'icon' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.unit' => 'required|string|max:20',
            'items.*.estimation_time' => 'required|integer|min:1'
        ]);

        Log::debug('ServiceController@store: Validasi berhasil', [
            'service_name' => $validated['name'],
            'service_type' => $validated['type'],
            'items_count' => count($validated['items'])
        ]);

        DB::beginTransaction();
        Log::debug('ServiceController@store: Memulai database transaction');

        try {
            // Insert service
            $serviceQuery = "
                INSERT INTO services (name, type, category, description, icon, color, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ";

            $serviceData = [
                $validated['name'],
                $validated['type'],
                $validated['category'],
                $validated['description'] ?? null,
                $validated['icon'],
                str_replace('bg-', '', $validated['color'])
            ];

            Log::debug('ServiceController@store: Menyimpan service ke database', $serviceData);
            DB::insert($serviceQuery, $serviceData);

            $serviceId = DB::getPdo()->lastInsertId();
            Log::debug('ServiceController@store: Service berhasil dibuat', ['service_id' => $serviceId]);

            // Insert service items
            $itemCount = 0;
            $totalItemPrice = 0;
            foreach ($validated['items'] as $index => $item) {
                $itemQuery = "
                    INSERT INTO service_items (service_id, name, price, unit, estimation_time, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ";

                $itemData = [
                    $serviceId,
                    $item['name'],
                    $item['price'],
                    $item['unit'],
                    $item['estimation_time']
                ];

                Log::debug('ServiceController@store: Menyimpan service item ' . ($index + 1), [
                    'item_name' => $item['name'],
                    'price' => $item['price'],
                    'unit' => $item['unit']
                ]);
                DB::insert($itemQuery, $itemData);
                $itemCount++;
                $totalItemPrice += (float) $item['price'];
            }

            Log::debug('ServiceController@store: Semua items berhasil disimpan', [
                'total_items' => $itemCount,
                'total_price_all_items' => $totalItemPrice
            ]);

            DB::commit();
            Log::debug('ServiceController@store: Database transaction committed');

            return response()->json([
                'success' => true,
                'message' => 'Service berhasil ditambahkan',
                'service_id' => $serviceId
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ServiceController@store: Error - ' . $e->getMessage(), [
                'request_data' => [
                    'name' => $request->name,
                    'type' => $request->type,
                    'category' => $request->category,
                    'items_count' => count($request->items ?? [])
                ],
                'trace' => $e->getTraceAsString(),
                'validated_data' => $validated
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan service: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleService(Request $request, $id)
    {
        Log::debug('ServiceController@toggleService: Mengubah status service', [
            'service_id' => $id,
            'new_active_status' => $request->active
        ]);

        $validated = $request->validate([
            'active' => 'required|boolean'
        ]);

        Log::debug('ServiceController@toggleService: Validasi berhasil', [
            'active_status' => $validated['active'] ? 'active' : 'inactive'
        ]);

        try {
            $query = "UPDATE services SET active = ?, updated_at = NOW() WHERE id = ?";
            Log::debug('ServiceController@toggleService: Mengupdate status service');
            $affected = DB::update($query, [$validated['active'], $id]);
            Log::debug('ServiceController@toggleService: Service update completed', [
                'affected_rows' => $affected
            ]);

            if ($affected === 0) {
                Log::warning('ServiceController@toggleService: Service tidak ditemukan', ['service_id' => $id]);
                return response()->json(['success' => false, 'message' => 'Service tidak ditemukan']);
            }

            // Juga update service items terkait
            $updateItemsQuery = "UPDATE service_items SET active = ? WHERE service_id = ?";
            Log::debug('ServiceController@toggleService: Mengupdate status service items');
            $itemsAffected = DB::update($updateItemsQuery, [$validated['active'], $id]);
            Log::debug('ServiceController@toggleService: Service items update completed', [
                'items_affected' => $itemsAffected
            ]);

            Log::debug('ServiceController@toggleService: Status service berhasil diupdate', [
                'service_id' => $id,
                'new_status' => $validated['active'] ? 'active' : 'inactive',
                'items_updated' => $itemsAffected
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Status service berhasil diupdate'
            ]);

        } catch (\Exception $e) {
            Log::error('ServiceController@toggleService: Error - ' . $e->getMessage(), [
                'service_id' => $id,
                'active_status' => $validated['active'],
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status service: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateServiceItem(Request $request, $serviceId, $itemId)
    {
        Log::debug('ServiceController@updateServiceItem: Mengupdate service item', [
            'service_id' => $serviceId,
            'item_id' => $itemId,
            'update_data' => [
                'name' => $request->name,
                'price' => $request->price,
                'unit' => $request->unit,
                'estimation_time' => $request->estimation_time
            ]
        ]);

        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'estimation_time' => 'required|integer|min:1'
        ]);

        Log::debug('ServiceController@updateServiceItem: Validasi berhasil', $validated);

        try {
            // Cek apakah service item exists
            $checkQuery = "SELECT id FROM service_items WHERE id = ? AND service_id = ?";
            $item = DB::select($checkQuery, [$itemId, $serviceId]);

            if (empty($item)) {
                Log::warning('ServiceController@updateServiceItem: Item service tidak ditemukan', [
                    'service_id' => $serviceId,
                    'item_id' => $itemId
                ]);
                return response()->json(['success' => false, 'message' => 'Item service tidak ditemukan']);
            }

            // Update item
            $updateQuery = "UPDATE service_items SET price = ?, name = ?, unit = ?, estimation_time = ?, updated_at = NOW() WHERE id = ?";
            Log::debug('ServiceController@updateServiceItem: Menjalankan update item');
            $affected = DB::update($updateQuery, [
                $validated['price'],
                $validated['name'],
                $validated['unit'],
                $validated['estimation_time'],
                $itemId
            ]);

            Log::debug('ServiceController@updateServiceItem: Update berhasil', [
                'affected_rows' => $affected,
                'item_id' => $itemId,
                'new_price' => $validated['price']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil diupdate'
            ]);

        } catch (\Exception $e) {
            Log::error('ServiceController@updateServiceItem: Error - ' . $e->getMessage(), [
                'service_id' => $serviceId,
                'item_id' => $itemId,
                'update_data' => $validated,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateService(Request $request, $id)
    {
        Log::debug('ServiceController@updateService: Mengupdate service', [
            'service_id' => $id,
            'update_data' => [
                'name' => $request->name,
                'category' => $request->category,
                'new_items_count' => count($request->new_items ?? [])
            ]
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'icon' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'new_items' => 'nullable|array',
            'new_items.*.name' => 'required|string|max:255',
            'new_items.*.price' => 'required|numeric|min:0',
            'new_items.*.unit' => 'required|string|max:20',
            'new_items.*.estimation_time' => 'required|integer|min:1'
        ]);

        Log::debug('ServiceController@updateService: Validasi berhasil', [
            'service_name' => $validated['name'],
            'new_items_count' => count($validated['new_items'] ?? [])
        ]);

        DB::beginTransaction();
        Log::debug('ServiceController@updateService: Memulai database transaction');

        try {
            // Update service details
            $query = "UPDATE services SET name = ?, category = ?, icon = ?, color = ?, description = ?, updated_at = NOW() WHERE id = ?";
            $serviceData = [
                $validated['name'],
                $validated['category'],
                $validated['icon'],
                str_replace('bg-', '', $validated['color']),
                $validated['description'] ?? null,
                $id
            ];

            Log::debug('ServiceController@updateService: Mengupdate service data');
            $affected = DB::update($query, $serviceData);
            Log::debug('ServiceController@updateService: Service update completed', [
                'affected_rows' => $affected
            ]);

            if ($affected === 0) {
                DB::rollBack();
                Log::warning('ServiceController@updateService: Service tidak ditemukan', ['service_id' => $id]);
                return response()->json(['success' => false, 'message' => 'Service tidak ditemukan']);
            }

            // Tambahkan item baru jika ada
            $newItemsCount = 0;
            $newItemsTotalPrice = 0;
            if (isset($validated['new_items']) && count($validated['new_items']) > 0) {
                Log::debug('ServiceController@updateService: Menambahkan items baru', [
                    'count' => count($validated['new_items'])
                ]);

                foreach ($validated['new_items'] as $index => $item) {
                    $itemQuery = "
                    INSERT INTO service_items (service_id, name, price, unit, estimation_time, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                    ";

                    $itemData = [
                        $id,
                        $item['name'],
                        $item['price'],
                        $item['unit'],
                        $item['estimation_time']
                    ];

                    Log::debug('ServiceController@updateService: Menyimpan item baru ' . ($index + 1), [
                        'item_name' => $item['name'],
                        'price' => $item['price']
                    ]);
                    DB::insert($itemQuery, $itemData);
                    $newItemsCount++;
                    $newItemsTotalPrice += (float) $item['price'];
                }
            }

            DB::commit();
            Log::debug('ServiceController@updateService: Database transaction committed', [
                'new_items_added' => $newItemsCount,
                'new_items_total_price' => $newItemsTotalPrice
            ]);

            $message = 'Service berhasil diupdate' .
                ($newItemsCount > 0 ? ' dengan ' . $newItemsCount . ' item baru' : '');

            Log::debug('ServiceController@updateService: ' . $message);
            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ServiceController@updateService: Error - ' . $e->getMessage(), [
                'service_id' => $id,
                'update_data' => [
                    'name' => $validated['name'],
                    'category' => $validated['category'],
                    'new_items_count' => count($validated['new_items'] ?? [])
                ],
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate service: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addServiceItem(Request $request, $serviceId)
    {
        Log::debug('ServiceController@addServiceItem: Menambah item ke service', [
            'service_id' => $serviceId,
            'item_data' => [
                'name' => $request->name,
                'price' => $request->price,
                'unit' => $request->unit
            ]
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|string|max:20',
            'estimation_time' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:service_categories,id'
        ]);

        Log::debug('ServiceController@addServiceItem: Validasi berhasil', [
            'item_name' => $validated['name'],
            'price' => $validated['price']
        ]);

        DB::beginTransaction();
        Log::debug('ServiceController@addServiceItem: Memulai database transaction');

        try {
            // Cek apakah service exists
            $serviceCheck = DB::select("SELECT id, name FROM services WHERE id = ?", [$serviceId]);
            if (empty($serviceCheck)) {
                Log::warning('ServiceController@addServiceItem: Service tidak ditemukan', ['service_id' => $serviceId]);
                return response()->json(['success' => false, 'message' => 'Service tidak ditemukan']);
            }

            Log::debug('ServiceController@addServiceItem: Service ditemukan', [
                'service_name' => $serviceCheck[0]->name
            ]);

            // Insert new service item
            $query = "
            INSERT INTO service_items (service_id, category_id, name, price, unit, estimation_time, description, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ";

            $itemData = [
                $serviceId,
                $validated['category_id'] ?? null,
                $validated['name'],
                $validated['price'],
                $validated['unit'],
                $validated['estimation_time'],
                $validated['description'] ?? null
            ];

            Log::debug('ServiceController@addServiceItem: Menyimpan service item');
            DB::insert($query, $itemData);

            $itemId = DB::getPdo()->lastInsertId();
            Log::debug('ServiceController@addServiceItem: Item berhasil dibuat', [
                'item_id' => $itemId,
                'service_id' => $serviceId
            ]);

            DB::commit();
            Log::debug('ServiceController@addServiceItem: Database transaction committed');

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil ditambahkan ke service',
                'item_id' => $itemId
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ServiceController@addServiceItem: Error - ' . $e->getMessage(), [
                'service_id' => $serviceId,
                'item_data' => $validated,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getServiceForEdit($id)
    {
        Log::debug('ServiceController@getServiceForEdit: Mengambil data service untuk edit', ['service_id' => $id]);

        try {
            // Get service dengan semua items (aktif & non-aktif)
            $serviceQuery = "
            SELECT 
                s.*,
                si.id as item_id,
                si.name as item_name,
                si.price,
                si.unit,
                si.estimation_time,
                si.description as item_description,
                si.active as item_active,
                si.category_id
            FROM services s
            LEFT JOIN service_items si ON s.id = si.service_id
            WHERE s.id = ?
            ORDER BY si.active DESC, si.name
            ";

            Log::debug('ServiceController@getServiceForEdit: Menjalankan query service untuk edit');
            $startTime = microtime(true);
            $services = DB::select($serviceQuery, [$id]);
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::debug('ServiceController@getServiceForEdit: Query selesai', [
                'results_count' => count($services),
                'execution_time_ms' => $executionTime
            ]);

            if (empty($services)) {
                Log::warning('ServiceController@getServiceForEdit: Service tidak ditemukan', ['service_id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Service tidak ditemukan'
                ], 404);
            }

            // Format data
            Log::debug('ServiceController@getServiceForEdit: Memulai formatting data service untuk edit');
            $serviceData = [
                'id' => $services[0]->id,
                'name' => $services[0]->name,
                'type' => $services[0]->type,
                'category' => $services[0]->category,
                'description' => $services[0]->description,
                'icon' => $services[0]->icon,
                'color' => $services[0]->color,
                'active' => (bool) $services[0]->active,
                'items' => []
            ];

            $itemCount = 0;
            $activeItemsCount = 0;
            $inactiveItemsCount = 0;
            foreach ($services as $service) {
                if ($service->item_id) {
                    $isActive = (bool) $service->item_active;
                    $serviceData['items'][] = [
                        'id' => $service->item_id,
                        'name' => $service->item_name,
                        'price' => (float) $service->price,
                        'unit' => $service->unit,
                        'estimation_time' => $service->estimation_time,
                        'description' => $service->item_description,
                        'active' => $isActive,
                        'category_id' => $service->category_id
                    ];
                    $itemCount++;
                    if ($isActive) {
                        $activeItemsCount++;
                    } else {
                        $inactiveItemsCount++;
                    }
                }
            }

            // Get available categories untuk dropdown
            $categoriesQuery = "SELECT id, name FROM service_categories WHERE active = 1 ORDER BY sort_order, name";
            Log::debug('ServiceController@getServiceForEdit: Mengambil categories untuk dropdown');
            $categories = DB::select($categoriesQuery);
            Log::debug('ServiceController@getServiceForEdit: Categories berhasil diambil', [
                'categories_count' => count($categories)
            ]);

            Log::debug('ServiceController@getServiceForEdit: Formatting selesai', [
                'service_name' => $serviceData['name'],
                'total_items' => $itemCount,
                'active_items' => $activeItemsCount,
                'inactive_items' => $inactiveItemsCount
            ]);

            Log::debug('ServiceController@getServiceForEdit: Proses selesai, mengembalikan response JSON');
            return response()->json([
                'success' => true,
                'service' => $serviceData,
                'categories' => $categories
            ]);

        } catch (\Exception $e) {
            Log::error('ServiceController@getServiceForEdit: Error - ' . $e->getMessage(), [
                'service_id' => $id,
                'trace' => $e->getTraceAsString(),
                'query_executed' => $serviceQuery ?? 'N/A'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data service: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        Log::debug('ServiceController@destroy: Menghapus service', ['service_id' => $id]);

        DB::beginTransaction();
        Log::debug('ServiceController@destroy: Memulai database transaction');

        try {
            // Soft delete service
            $serviceQuery = "UPDATE services SET active = 0, updated_at = NOW() WHERE id = ?";
            Log::debug('ServiceController@destroy: Melakukan soft delete service');
            $affected = DB::update($serviceQuery, [$id]);
            Log::debug('ServiceController@destroy: Service soft delete completed', [
                'affected_rows' => $affected
            ]);

            if ($affected === 0) {
                Log::warning('ServiceController@destroy: Service tidak ditemukan', ['service_id' => $id]);
                return response()->json(['success' => false, 'message' => 'Service tidak ditemukan']);
            }

            // Soft delete related items
            $itemsQuery = "UPDATE service_items SET active = 0, updated_at = NOW() WHERE service_id = ?";
            Log::debug('ServiceController@destroy: Melakukan soft delete service items');
            $itemsAffected = DB::update($itemsQuery, [$id]);
            Log::debug('ServiceController@destroy: Service items soft delete completed', [
                'items_affected' => $itemsAffected
            ]);

            DB::commit();
            Log::debug('ServiceController@destroy: Database transaction committed', [
                'service_id' => $id,
                'items_deactivated' => $itemsAffected
            ]);

            Log::debug('ServiceController@destroy: Service berhasil dihapus (soft delete)');
            return response()->json([
                'success' => true,
                'message' => 'Service berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ServiceController@destroy: Error - ' . $e->getMessage(), [
                'service_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus service: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getServiceTypes()
    {
        Log::debug('ServiceController@getServiceTypes: Mengambil daftar service types');

        $types = [
            ['value' => 'kiloan', 'label' => 'Laundry Kiloan', 'icon' => 'fas fa-weight'],
            ['value' => 'satuan', 'label' => 'Laundry Satuan', 'icon' => 'fas fa-tshirt'],
            ['value' => 'khusus', 'label' => 'Layanan Khusus', 'icon' => 'fas fa-star']
        ];

        Log::debug('ServiceController@getServiceTypes: Mengembalikan service types', [
            'types_count' => count($types),
            'types_list' => array_column($types, 'value')
        ]);
        return response()->json([
            'success' => true,
            'types' => $types
        ]);
    }

    public function getServiceCategories()
    {
        Log::debug('ServiceController@getServiceCategories: Mengambil daftar service categories');

        try {
            $categoriesQuery = "
            SELECT DISTINCT category as name, 
                   COUNT(*) as service_count
            FROM services 
            WHERE active = 1 
            GROUP BY category 
            ORDER BY service_count DESC, name
            ";

            Log::debug('ServiceController@getServiceCategories: Menjalankan query categories');
            $startTime = microtime(true);
            $categories = DB::select($categoriesQuery);
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::debug('ServiceController@getServiceCategories: Query selesai', [
                'categories_count' => count($categories),
                'execution_time_ms' => $executionTime
            ]);

            // Map category values ke label yang lebih user-friendly
            $categoryMapping = [
                'regular' => 'Regular',
                'special' => 'Khusus',
                'cuci' => 'Cuci',
                'setrika' => 'Setrika',
                'dry_clean' => 'Dry Clean',
                'khusus' => 'Khusus',
                'lainnya' => 'Lainnya'
            ];

            $categoryIcons = [
                'regular' => 'fas fa-tshirt',
                'special' => 'fas fa-star',
                'cuci' => 'fas fa-soap',
                'setrika' => 'fas fa-fire',
                'dry_clean' => 'fas fa-wind',
                'khusus' => 'fas fa-star',
                'lainnya' => 'fas fa-ellipsis-h'
            ];

            $formattedCategories = array_map(function ($category) use ($categoryMapping, $categoryIcons) {
                $originalName = $category->name;
                $displayName = $categoryMapping[$originalName] ?? ucfirst($originalName);

                return [
                    'name' => $displayName,
                    'original_name' => $originalName,
                    'icon' => $categoryIcons[$originalName] ?? 'fas fa-tag',
                    'service_count' => $category->service_count
                ];
            }, $categories);

            Log::debug('ServiceController@getServiceCategories: Formatting categories selesai', [
                'formatted_categories_count' => count($formattedCategories)
            ]);
            return response()->json([
                'success' => true,
                'categories' => $formattedCategories
            ]);

        } catch (\Exception $e) {
            Log::error('ServiceController@getServiceCategories: Error - ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'query_executed' => $categoriesQuery ?? 'N/A'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat kategori: ' . $e->getMessage()
            ], 500);
        }
    }
}