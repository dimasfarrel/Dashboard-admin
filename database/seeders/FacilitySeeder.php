<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            // Furnitur
            ['name' => 'Kasur Single',      'icon' => 'bi-person',              'category' => 'furnitur'],
            ['name' => 'Kasur Double',      'icon' => 'bi-people',              'category' => 'furnitur'],
            ['name' => 'Lemari Pakaian',    'icon' => 'bi-box',                 'category' => 'furnitur'],
            ['name' => 'Meja Belajar',      'icon' => 'bi-laptop',              'category' => 'furnitur'],
            ['name' => 'Kursi',             'icon' => 'bi-chair',               'category' => 'furnitur'],
            ['name' => 'Meja Rias',         'icon' => 'bi-stars',               'category' => 'furnitur'],
            ['name' => 'Rak Sepatu',        'icon' => 'bi-bag',                 'category' => 'furnitur'],
            ['name' => 'Sofa/Kursi Santai', 'icon' => 'bi-house-heart',         'category' => 'furnitur'],

            // Elektronik
            ['name' => 'AC',                'icon' => 'bi-wind',                'category' => 'elektronik'],
            ['name' => 'Kipas Angin',       'icon' => 'bi-fan',                 'category' => 'elektronik'],
            ['name' => 'TV',                'icon' => 'bi-tv',                  'category' => 'elektronik'],
            ['name' => 'Kulkas',            'icon' => 'bi-snow',                'category' => 'elektronik'],
            ['name' => 'Water Heater',      'icon' => 'bi-thermometer-sun',     'category' => 'elektronik'],
            ['name' => 'Dispenser',         'icon' => 'bi-cup-hot',             'category' => 'elektronik'],

            // Kamar Mandi
            ['name' => 'Kamar Mandi Dalam', 'icon' => 'bi-droplet',             'category' => 'kamar_mandi'],
            ['name' => 'WC Duduk',          'icon' => 'bi-toilet',              'category' => 'kamar_mandi'],
            ['name' => 'Shower',            'icon' => 'bi-water',               'category' => 'kamar_mandi'],
            ['name' => 'Water Heater Shower','icon'=> 'bi-thermometer-sun',     'category' => 'kamar_mandi'],

            // Lainnya
            ['name' => 'WiFi',              'icon' => 'bi-wifi',                'category' => 'lainnya'],
            ['name' => 'Balkon',            'icon' => 'bi-house-door',          'category' => 'lainnya'],
            ['name' => 'Dapur Bersama',     'icon' => 'bi-fire',                'category' => 'lainnya'],
            ['name' => 'Parkir Motor',      'icon' => 'bi-bicycle',             'category' => 'lainnya'],
            ['name' => 'Parkir Mobil',      'icon' => 'bi-car-front',           'category' => 'lainnya'],
            ['name' => 'CCTV',             'icon' => 'bi-camera-video',         'category' => 'lainnya'],
            ['name' => 'Jendela Lebar',     'icon' => 'bi-wind',                'category' => 'lainnya'],
        ];

        foreach ($facilities as $facility) {
            Facility::firstOrCreate(['name' => $facility['name']], $facility);
        }
    }
}
