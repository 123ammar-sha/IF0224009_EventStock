<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Flightcase;
use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Kategori
        $catAudio = Category::create(['name' => 'Audio Equipment', 'type' => 'asset']);
        $catCable = Category::create(['name' => 'Cables', 'type' => 'asset']);
        $catConsumable = Category::create(['name' => 'Consumables', 'type' => 'consumable']);

        // 2. Buat Flightcase
        $fcAudioA = Flightcase::create(['name' => 'Flightcase Audio A', 'description' => 'Box khusus mic dan receiver panggung utama']);
        $fcCableBox = Flightcase::create(['name' => 'Cable Trunk 01', 'description' => 'Box kabel XLR dan Power']);

        // 3. Buat Data Barang (Items)

        // Barang di dalam Flightcase Audio A
        Item::create([
            'category_id' => $catAudio->id,
            'flightcase_id' => $fcAudioA->id,
            'sku' => 'AUD-MIC-001',
            'name' => 'Shure SM58 Dynamic Mic',
            'total_qty' => 10,
            'available_qty' => 10,
        ]);

        // Barang di dalam Cable Trunk
        Item::create([
            'category_id' => $catCable->id,
            'flightcase_id' => $fcCableBox->id,
            'sku' => 'CBL-XLR-10M',
            'name' => 'Kabel XLR 10 Meter',
            'total_qty' => 50,
            'available_qty' => 50,
        ]);

        // Barang Aset Tanpa Flightcase (Berdiri Sendiri)
        Item::create([
            'category_id' => $catAudio->id,
            'flightcase_id' => null,
            'sku' => 'AUD-SPK-001',
            'name' => 'Speaker Aktif Yamaha DBR15',
            'total_qty' => 4,
            'available_qty' => 4,
        ]);

        // Barang Consumable (Habis Pakai)
        Item::create([
            'category_id' => $catConsumable->id,
            'flightcase_id' => null,
            'sku' => 'CNS-GF-BLK',
            'name' => 'Gaffer Tape Black 50mm',
            'total_qty' => 20,
            'available_qty' => 20,
        ]);
    }
}
