<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use App\Models\Flightcase;
use App\Models\Item;
use App\Models\Manifest;
use App\Models\ManifestItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ========================================
        // 1. KATEGORI
        // ========================================
        $catAudio = Category::firstOrCreate(['name' => 'Audio Equipment', 'type' => 'asset']);
        $catLighting = Category::firstOrCreate(['name' => 'Lighting', 'type' => 'asset']);
        $catCable = Category::firstOrCreate(['name' => 'Cables & Accessories', 'type' => 'asset']);
        $catStage = Category::firstOrCreate(['name' => 'Stage & Rigging', 'type' => 'asset']);
        $catConsumable = Category::firstOrCreate(['name' => 'Consumables', 'type' => 'consumable']);

        $this->command->info('✅ 5 Kategori berhasil dibuat');

        // ========================================
        // 2. FLIGHTCASES
        // ========================================
        // $fcAudio = Flightcase::create(['code' => 'FC-AUDIO-01', 'name' => 'Flightcase Audio A', 'description' => 'Box khusus mic dan receiver']);
        // $fcLighting = Flightcase::create(['code' => 'FC-LIGHT-01', 'name' => 'Lighting Truss Kit', 'description' => 'Box untuk lampu dan truss kecil']);
        // $fcCable = Flightcase::create(['code' => 'FC-CABLE-01', 'name' => 'Cable Trunk 01', 'description' => 'Box kabel XLR dan Power']);

        // $this->command->info('✅ 3 Flightcase berhasil dibuat');

        // ========================================
        // 3. ITEMS
        // ========================================

        // --- ITEMS DALAM FLIGHTCASE AUDIO ---
        $itemMic = Item::create([
            'category_id' => $catAudio->id,
            'name' => 'Shure SM58 Dynamic Mic',
            'sku' => 'AUD-MIC-001',
            'total_qty' => 10,
            'available_qty' => 10,
            'status' => 'available',
        ]);
        // Attach ke pivot flightcase_item
        // DB::table('flightcase_item')->insert([
        //     // 'flightcase_id' => $fcAudio->id,
        //     'item_id' => $itemMic->id,
        //     'qty' => 6,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        $itemReceiver = Item::create([
            'category_id' => $catAudio->id,
            'name' => 'Sennheiser EW100 G4 Wireless Receiver',
            'sku' => 'AUD-RX-002',
            'total_qty' => 4,
            'available_qty' => 4,
            'status' => 'available',
        ]);
        // DB::table('flightcase_item')->insert([
        //     'flightcase_id' => $fcAudio->id,
        //     'item_id' => $itemReceiver->id,
        //     'qty' => 2,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // --- ITEMS DALAM FLIGHTCASE LIGHTING ---
        $itemLight = Item::create([
            'category_id' => $catLighting->id,
            'name' => 'Martin MAC Aura XB LED Wash',
            'sku' => 'LGH-FIX-001',
            'total_qty' => 8,
            'available_qty' => 8,
            'status' => 'available',
        ]);
        // DB::table('flightcase_item')->insert([
        //     'flightcase_id' => $fcLighting->id,
        //     'item_id' => $itemLight->id,
        //     'qty' => 4,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // --- ITEMS DALAM FLIGHTCASE CABLE ---
        $itemXLR = Item::create([
            'category_id' => $catCable->id,
            'name' => 'Kabel XLR 10 Meter',
            'sku' => 'CBL-XLR-10M',
            'total_qty' => 50,
            'available_qty' => 50,
            'status' => 'available',
        ]);
        // DB::table('flightcase_item')->insert([
        //     'flightcase_id' => $fcCable->id,
        //     'item_id' => $itemXLR->id,
        //     'qty' => 20,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        $itemPower = Item::create([
            'category_id' => $catCable->id,
            'name' => 'Kabel Power 5 Meter',
            'sku' => 'CBL-PWR-05M',
            'total_qty' => 30,
            'available_qty' => 30,
            'status' => 'available',
        ]);
        // DB::table('flightcase_item')->insert([
        //     'flightcase_id' => $fcCable->id,
        //     'item_id' => $itemPower->id,
        //     'qty' => 10,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // --- ITEMS SATUAN (TIDAK DALAM FLIGHTCASE) ---
        Item::create([
            'category_id' => $catAudio->id,
            'name' => 'Speaker Aktif Yamaha DBR15',
            'sku' => 'AUD-SPK-001',
            'total_qty' => 4,
            'available_qty' => 4,
            'status' => 'available',
        ]);

        Item::create([
            'category_id' => $catAudio->id,
            'name' => 'Mixer Digital Behringer X32',
            'sku' => 'AUD-MIX-001',
            'total_qty' => 2,
            'available_qty' => 2,
            'status' => 'available',
        ]);

        Item::create([
            'category_id' => $catLighting->id,
            'name' => 'Follow Spot Light 2kW',
            'sku' => 'LGH-SPT-001',
            'total_qty' => 3,
            'available_qty' => 3,
            'status' => 'available',
        ]);

        Item::create([
            'category_id' => $catStage->id,
            'name' => 'Truss Aluminium 3 Meter',
            'sku' => 'STG-TRS-3M',
            'total_qty' => 20,
            'available_qty' => 20,
            'status' => 'available',
        ]);

        Item::create([
            'category_id' => $catStage->id,
            'name' => 'Rigging Clamp Set',
            'sku' => 'STG-CLM-001',
            'total_qty' => 40,
            'available_qty' => 40,
            'status' => 'available',
        ]);

        Item::create([
            'category_id' => $catCable->id,
            'name' => 'Kabel HDMI 15 Meter',
            'sku' => 'CBL-HDMI-15M',
            'total_qty' => 10,
            'available_qty' => 10,
            'status' => 'available',
        ]);

        // --- CONSUMABLE ITEMS ---
        Item::create([
            'category_id' => $catConsumable->id,
            'name' => 'Gaffer Tape Black 50mm',
            'sku' => 'CNS-GF-BLK',
            'total_qty' => 20,
            'available_qty' => 20,
            'status' => 'available',
        ]);

        Item::create([
            'category_id' => $catConsumable->id,
            'name' => 'Baterai AA Duracell (Box 12 pcs)',
            'sku' => 'CNS-BAT-AA',
            'total_qty' => 15,
            'available_qty' => 15,
            'status' => 'available',
        ]);

        Item::create([
            'category_id' => $catConsumable->id,
            'name' => 'Cable Tie 30cm (Paket 100)',
            'sku' => 'CNS-TIE-30',
            'total_qty' => 10,
            'available_qty' => 10,
            'status' => 'available',
        ]);

        $this->command->info('✅ 15 Item berhasil dibuat (termasuk bundling flightcase via pivot)');

        // ========================================
        // 4. EVENTS
        // ========================================
        $event1 = Event::create([
            'name' => 'Java Jazz Festival 2026',
            'venue' => 'JIEXPO Kemayoran, Jakarta',
            'start_date' => '2026-07-15 08:00:00',
            'end_date' => '2026-07-17 23:00:00',
            'status' => 'upcoming',
        ]);

        $event2 = Event::create([
            'name' => 'Soundrenaline 2026',
            'venue' => 'Garuda Wisnu Kencana, Bali',
            'start_date' => '2026-08-20 08:00:00',
            'end_date' => '2026-08-22 23:00:00',
            'status' => 'upcoming',
        ]);

        $event3 = Event::create([
            'name' => 'Konsert Dewa 19 - 30th Anniversary',
            'venue' => 'Stadion Utama GBK, Jakarta',
            'start_date' => '2026-09-10 14:00:00',
            'end_date' => '2026-09-10 23:00:00',
            'status' => 'upcoming',
        ]);

        $eventCompleted = Event::create([
            'name' => 'Pesta Rakyat Kemerdekaan 2025',
            'venue' => 'Lapangan Monas, Jakarta',
            'start_date' => '2025-08-17 06:00:00',
            'end_date' => '2025-08-17 22:00:00',
            'status' => 'completed',
        ]);

        $this->command->info('✅ 4 Event berhasil dibuat');

        // ========================================
        // 5. OUTBOUND TRANSACTION (CONTOH)
        // ========================================
        $crew = User::where('role', 'field_crew')->first();
        $manager = User::where('role', 'warehouse_manager')->first();

        // OUTBOUND 1: Java Jazz Festival - Kirim beberapa item satuan
        $outbound1 = Manifest::create([
            'manifest_number' => 'OUT-20260715-001',
            'event_id' => $event1->id,
            'user_id' => $crew->id,
            'type' => 'outbound',
            'status' => 'in_progress',
        ]);

        // Ambil item yang tersedia
        $speaker = Item::where('sku', 'AUD-SPK-001')->first();
        $mixer = Item::where('sku', 'AUD-MIX-001')->first();
        $truss = Item::where('sku', 'STG-TRS-3M')->first();
        $gaffer = Item::where('sku', 'CNS-GF-BLK')->first();

        // Outbound: 2 speaker, 1 mixer, 5 truss, 3 gaffer tape
        if ($speaker && $speaker->available_qty >= 2) {
            ManifestItem::create([
                'manifest_id' => $outbound1->id,
                'item_id' => $speaker->id,
                'qty_requested' => 2,
                'qty_actual' => 2,
                'condition' => 'good',
            ]);
            $speaker->available_qty -= 2;
            $speaker->status = 'on_duty';
            $speaker->save();
        }

        if ($mixer && $mixer->available_qty >= 1) {
            ManifestItem::create([
                'manifest_id' => $outbound1->id,
                'item_id' => $mixer->id,
                'qty_requested' => 1,
                'qty_actual' => 1,
                'condition' => 'good',
            ]);
            $mixer->available_qty -= 1;
            $mixer->status = 'on_duty';
            $mixer->save();
        }

        if ($truss && $truss->available_qty >= 5) {
            ManifestItem::create([
                'manifest_id' => $outbound1->id,
                'item_id' => $truss->id,
                'qty_requested' => 5,
                'qty_actual' => 5,
                'condition' => 'good',
            ]);
            $truss->available_qty -= 5;
            $truss->status = 'on_duty';
            $truss->save();
        }

        if ($gaffer && $gaffer->available_qty >= 3) {
            ManifestItem::create([
                'manifest_id' => $outbound1->id,
                'item_id' => $gaffer->id,
                'qty_requested' => 3,
                'qty_actual' => 3,
                'condition' => 'good',
            ]);
            $gaffer->available_qty -= 3;
            $gaffer->total_qty -= 3; // Consumable: kurangi total_qty
            $gaffer->save();
        }

        $this->command->info('✅ OUTBOUND 1 (Java Jazz) - 4 item berhasil dikirim');

        // OUTBOUND 2: Event Completed (sudah selesai & di-inbound)
        $outbound2 = Manifest::create([
            'manifest_number' => 'OUT-20250817-001',
            'event_id' => $eventCompleted->id,
            'user_id' => $crew->id,
            'type' => 'outbound',
            'status' => 'completed', // Sudah di-inbound
        ]);

        $lightItem = Item::where('sku', 'LGH-FIX-001')->first();
        $xlrItem = Item::where('sku', 'CBL-XLR-10M')->first();

        if ($lightItem && $lightItem->available_qty >= 4) {
            ManifestItem::create([
                'manifest_id' => $outbound2->id,
                'item_id' => $lightItem->id,
                'qty_requested' => 4,
                'qty_actual' => 4,
                'condition' => 'good',
            ]);
            $lightItem->available_qty -= 4;
            $lightItem->status = 'on_duty';
            $lightItem->save();
        }

        if ($xlrItem && $xlrItem->available_qty >= 10) {
            ManifestItem::create([
                'manifest_id' => $outbound2->id,
                'item_id' => $xlrItem->id,
                'qty_requested' => 10,
                'qty_actual' => 10,
                'condition' => 'good',
            ]);
            $xlrItem->available_qty -= 10;
            $xlrItem->status = 'on_duty';
            $xlrItem->save();
        }

        $this->command->info('✅ OUTBOUND 2 (Pesta Rakyat) - 2 item berhasil dikirim');

        // ========================================
        // 6. INBOUND TRANSACTION (CONTOH)
        // ========================================
        // Inbound untuk OUTBOUND 2 (Event Completed)
        $inbound1 = Manifest::create([
            'manifest_number' => 'INB-20250818-001',
            'event_id' => $eventCompleted->id,
            'user_id' => $manager->id,
            'type' => 'inbound',
            'status' => 'completed',
            'outbound_manifest_id' => $outbound2->id,
        ]);

        // Light items kembali baik semua
        $lightItem->available_qty += 4;
        $lightItem->status = 'available';
        $lightItem->save();

        ManifestItem::create([
            'manifest_id' => $inbound1->id,
            'item_id' => $lightItem->id,
            'qty_requested' => 0,
            'qty_actual' => 4,
            'condition' => 'good',
            'notes' => null,
        ]);

        // XLR cable: 8 kembali baik, 2 hilang
        $xlrItem->available_qty += 8;
        $xlrItem->status = 'available';
        $xlrItem->total_qty -= 2; // hilang permanen
        $xlrItem->save();

        ManifestItem::create([
            'manifest_id' => $inbound1->id,
            'item_id' => $xlrItem->id,
            'qty_requested' => 0,
            'qty_actual' => 8,
            'condition' => 'good',
            'notes' => null,
        ]);

        ManifestItem::create([
            'manifest_id' => $inbound1->id,
            'item_id' => $xlrItem->id,
            'qty_requested' => 0,
            'qty_actual' => 2,
            'condition' => 'lost',
            'notes' => 'Kabel hilang saat loading out, tidak ditemukan di venue',
        ]);

        // Update outbound status jadi has_issue
        $outbound2->update(['status' => 'has_issue']);

        $this->command->info('✅ INBOUND (Pesta Rakyat) - Barang kembali, 2 kabel XLR hilang');

        // ========================================
        // SUMMARY
        // ========================================
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('  🎯 SEEDING DEMO DATA SELESAI!');
        $this->command->info('========================================');
        $this->command->info('  Kategori     : 5');
        $this->command->info('  Flightcase   : 3 (dengan pivot items)');
        $this->command->info('  Items        : 15');
        $this->command->info('  Events       : 4 (3 upcoming, 1 completed)');
        $this->command->info('  Outbound     : 2');
        $this->command->info('  Inbound      : 1');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('  📌 Items on_duty sekarang:');
        $this->command->info('     - Speaker Yamaha DBR15 (2 pcs) -> Java Jazz');
        $this->command->info('     - Mixer X32 (1 pcs) -> Java Jazz');
        $this->command->info('     - Truss 3M (5 pcs) -> Java Jazz');
        $this->command->info('     - Gaffer Tape (3 pcs) -> Java Jazz (consumable)');
        $this->command->info('');
        $this->command->info('  📌 Items dengan issue:');
        $this->command->info('     - Kabel XLR 10M (2 pcs hilang) -> Pesta Rakyat');
        $this->command->info('========================================');
    }
}
