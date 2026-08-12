<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use App\Models\RoomType;
use App\Models\Facility;
use App\Models\MaintenanceCategory;
use App\Models\ExpenseCategory;
use App\Models\TenantCustomField;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConfigController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'floors');

        $floors = Floor::orderBy('number')->get();
        $roomTypes = RoomType::orderBy('name')->get();
        $facilities = Facility::orderBy('category')->orderBy('name')->get();
        $maintenanceCategories = MaintenanceCategory::orderBy('name')->get();
        $expenseCategories = ExpenseCategory::orderBy('name')->get();
        $tenantCustomFields = TenantCustomField::orderBy('sort_order')->orderBy('name')->get();
        $lodgingDefaultPrice = (int) AppSetting::get('lodging_default_price', 150000);

        return view('settings.index', compact(
            'activeTab', 'floors', 'roomTypes', 'facilities', 
            'maintenanceCategories', 'expenseCategories',
            'tenantCustomFields', 'lodgingDefaultPrice'
        ));
    }

    // ===== FLOORS CRUD =====
    public function storeFloor(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|integer|unique:floors,number',
            'name' => 'required|string|max:255',
        ]);

        Floor::create($validated);
        return redirect()->route('settings.index', ['tab' => 'floors'])->with('success', 'Lantai berhasil ditambahkan!');
    }

    public function updateFloor(Request $request, Floor $floor)
    {
        $validated = $request->validate([
            'number' => 'required|integer|unique:floors,number,' . $floor->id,
            'name' => 'required|string|max:255',
        ]);

        $floor->update($validated);
        return redirect()->route('settings.index', ['tab' => 'floors'])->with('success', 'Lantai berhasil diperbarui!');
    }

    public function destroyFloor(Floor $floor)
    {
        $floor->delete();
        return redirect()->route('settings.index', ['tab' => 'floors'])->with('success', 'Lantai berhasil dihapus!');
    }

    // ===== ROOM TYPES CRUD =====
    public function storeRoomType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:room_types,name',
            'description' => 'nullable|string',
        ]);

        RoomType::create($validated);
        return redirect()->route('settings.index', ['tab' => 'room_types'])->with('success', 'Tipe kamar berhasil ditambahkan!');
    }

    public function updateRoomType(Request $request, RoomType $roomType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:room_types,name,' . $roomType->id,
            'description' => 'nullable|string',
        ]);

        $roomType->update($validated);
        return redirect()->route('settings.index', ['tab' => 'room_types'])->with('success', 'Tipe kamar berhasil diperbarui!');
    }

    public function destroyRoomType(RoomType $roomType)
    {
        $roomType->delete();
        return redirect()->route('settings.index', ['tab' => 'room_types'])->with('success', 'Tipe kamar berhasil dihapus!');
    }

    // ===== FACILITIES CRUD =====
    public function storeFacility(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:facilities,name',
            'category' => 'required|string',
            'icon' => 'nullable|string|max:255',
        ]);

        if (empty($validated['icon'])) {
            $validated['icon'] = 'bi-check-circle';
        }

        Facility::create($validated);
        return redirect()->route('settings.index', ['tab' => 'facilities'])->with('success', 'Fasilitas berhasil ditambahkan!');
    }

    public function updateFacility(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:facilities,name,' . $facility->id,
            'category' => 'required|string',
            'icon' => 'nullable|string|max:255',
        ]);

        if (empty($validated['icon'])) {
            $validated['icon'] = 'bi-check-circle';
        }

        $facility->update($validated);
        return redirect()->route('settings.index', ['tab' => 'facilities'])->with('success', 'Fasilitas berhasil diperbarui!');
    }

    public function destroyFacility(Facility $facility)
    {
        $facility->delete();
        return redirect()->route('settings.index', ['tab' => 'facilities'])->with('success', 'Fasilitas berhasil dihapus!');
    }

    // ===== MAINTENANCE CATEGORIES CRUD =====
    public function storeMaintenanceCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:maintenance_categories,name',
        ]);

        $validated['slug'] = Str::slug($validated['name'], '_');

        MaintenanceCategory::create($validated);
        return redirect()->route('settings.index', ['tab' => 'maintenance_categories'])->with('success', 'Kategori maintenance berhasil ditambahkan!');
    }

    public function updateMaintenanceCategory(Request $request, MaintenanceCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:maintenance_categories,name,' . $category->id,
        ]);

        $validated['slug'] = Str::slug($validated['name'], '_');

        $category->update($validated);
        return redirect()->route('settings.index', ['tab' => 'maintenance_categories'])->with('success', 'Kategori maintenance berhasil diperbarui!');
    }

    public function destroyMaintenanceCategory(MaintenanceCategory $category)
    {
        $category->delete();
        return redirect()->route('settings.index', ['tab' => 'maintenance_categories'])->with('success', 'Kategori maintenance berhasil dihapus!');
    }

    // ===== EXPENSE CATEGORIES CRUD =====
    public function storeExpenseCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name',
            'icon' => 'nullable|string|max:255',
        ]);

        if (empty($validated['icon'])) {
            $validated['icon'] = 'bi-cash';
        }

        $validated['slug'] = Str::slug($validated['name'], '_');

        ExpenseCategory::create($validated);
        return redirect()->route('settings.index', ['tab' => 'expense_categories'])->with('success', 'Kategori pengeluaran berhasil ditambahkan!');
    }

    public function updateExpenseCategory(Request $request, ExpenseCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $category->id,
            'icon' => 'nullable|string|max:255',
        ]);

        if (empty($validated['icon'])) {
            $validated['icon'] = 'bi-cash';
        }

        $validated['slug'] = Str::slug($validated['name'], '_');

        $category->update($validated);
        return redirect()->route('settings.index', ['tab' => 'expense_categories'])->with('success', 'Kategori pengeluaran berhasil diperbarui!');
    }

    public function destroyExpenseCategory(ExpenseCategory $category)
    {
        $category->delete();
        return redirect()->route('settings.index', ['tab' => 'expense_categories'])->with('success', 'Kategori pengeluaran berhasil dihapus!');
    }

    // ===== TENANT CUSTOM FIELDS CRUD =====
    public function storeTenantField(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:text,number,date,textarea',
            'is_required' => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
            'placeholder' => 'nullable|string|max:255',
        ]);

        $validated['field_key']   = Str::slug($validated['name'], '_');
        $validated['is_required'] = $request->boolean('is_required');
        $validated['sort_order']  = $validated['sort_order'] ?? 0;

        TenantCustomField::create($validated);
        return redirect()->route('settings.index', ['tab' => 'tenant_fields'])->with('success', 'Field penyewa berhasil ditambahkan!');
    }

    public function updateTenantField(Request $request, TenantCustomField $tenantField)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:text,number,date,textarea',
            'is_required' => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
            'placeholder' => 'nullable|string|max:255',
        ]);

        $validated['field_key']   = Str::slug($validated['name'], '_');
        $validated['is_required'] = $request->boolean('is_required');
        $validated['sort_order']  = $validated['sort_order'] ?? 0;

        $tenantField->update($validated);
        return redirect()->route('settings.index', ['tab' => 'tenant_fields'])->with('success', 'Field penyewa berhasil diperbarui!');
    }

    public function destroyTenantField(TenantCustomField $tenantField)
    {
        $tenantField->delete();
        return redirect()->route('settings.index', ['tab' => 'tenant_fields'])->with('success', 'Field penyewa berhasil dihapus!');
    }

    // ===== APP SETTINGS =====
    public function updateLodgingPrice(Request $request)
    {
        $request->validate([
            'lodging_default_price' => 'required|numeric|min:0',
        ]);

        AppSetting::set('lodging_default_price', $request->lodging_default_price);
        return redirect()->route('settings.index', ['tab' => 'lodging'])->with('success', 'Harga default penginapan berhasil diperbarui!');
    }
}
