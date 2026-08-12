<?php

namespace Database\Seeders;

use App\Models\Floor;
use App\Models\RoomType;
use App\Models\MaintenanceCategory;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ConfigMetadataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Floors
        $floors = [
            ['number' => 1, 'name' => 'Lantai 1'],
            ['number' => 2, 'name' => 'Lantai 2'],
            ['number' => 3, 'name' => 'Lantai 3'],
            ['number' => 4, 'name' => 'Lantai 4'],
            ['number' => 5, 'name' => 'Lantai 5'],
        ];
        foreach ($floors as $f) {
            Floor::firstOrCreate(['number' => $f['number']], $f);
        }

        // 2. Room Types
        $roomTypes = [
            ['name' => 'Standard', 'description' => 'Kamar standard dengan fasilitas dasar'],
            ['name' => 'Deluxe',   'description' => 'Kamar lebih luas dengan AC dan Kamar Mandi Dalam'],
            ['name' => 'VIP',      'description' => 'Kamar premium dengan Water Heater, Kulkas, TV, dll'],
            ['name' => 'Suite',    'description' => 'Kamar eksklusif ukuran besar dengan balkon pribadi'],
        ];
        foreach ($roomTypes as $rt) {
            RoomType::firstOrCreate(['name' => $rt['name']], $rt);
        }

        // 3. Maintenance Categories
        $mCategories = [
            ['name' => 'Elektronik',        'slug' => 'elektronik'],
            ['name' => 'Kasur & Furniture', 'slug' => 'kasur_furniture'],
            ['name' => 'Plumbing (Air)',    'slug' => 'plumbing'],
            ['name' => 'Cat & Dinding',     'slug' => 'cat_dinding'],
            ['name' => 'Pintu & Jendela',   'slug' => 'pintu_jendela'],
            ['name' => 'AC & Pendingin',    'slug' => 'ac_pendingin'],
            ['name' => 'Lain-lain',         'slug' => 'lain-lain'],
        ];
        foreach ($mCategories as $mc) {
            MaintenanceCategory::firstOrCreate(['slug' => $mc['slug']], $mc);
        }

        // 4. Expense Categories
        $eCategories = [
            ['name' => 'Listrik',           'slug' => 'listrik',      'icon' => 'bi-lightning-charge'],
            ['name' => 'Air / PDAM',        'slug' => 'air',          'icon' => 'bi-droplet'],
            ['name' => 'Internet / WiFi',   'slug' => 'internet',     'icon' => 'bi-wifi'],
            ['name' => 'Kebersihan',        'slug' => 'kebersihan',   'icon' => 'bi-trash'],
            ['name' => 'Keamanan',          'slug' => 'keamanan',     'icon' => 'bi-shield-check'],
            ['name' => 'Pajak & Administrasi','slug' => 'pajak',      'icon' => 'bi-receipt'],
            ['name' => 'Renovasi',          'slug' => 'renovasi',     'icon' => 'bi-tools'],
            ['name' => 'Perlengkapan',      'slug' => 'perlengkapan', 'icon' => 'bi-box'],
            ['name' => 'Lain-lain',         'slug' => 'lain-lain',    'icon' => 'bi-three-dots'],
        ];
        foreach ($eCategories as $ec) {
            ExpenseCategory::firstOrCreate(['slug' => $ec['slug']], $ec);
        }
    }
}
