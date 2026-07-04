<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use App\Models\Flightcase;
use App\Models\Item;
use App\Models\Manifest;
use App\Models\ManifestItem;
use App\Models\StockTransaction;
use App\Models\IncidentLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LargeGudangSimulationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign keys for safety while seeding large demo data if needed
        // but since we are inserting fresh, let's keep it safe.

        $this->command->warn('Memulai seeding data simulasi gudang besar (100+ items, 50+ transaksi)...');

        // 1. Get or create Users
        $admin = User::where('role', 'super_admin')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Super Admin',
                'email' => 'admin@eventstock.test',
                'password' => bcrypt('password'),
                'role' => 'super_admin',
            ]);
        }
        $manager = User::where('role', 'warehouse_manager')->first() ?: User::create([
            'name' => 'Joni Warehouse Manager',
            'email' => 'manager@eventstock.test',
            'password' => bcrypt('password'),
            'role' => 'warehouse_manager',
        ]);
        $crew = User::where('role', 'field_crew')->first() ?: User::create([
            'name' => 'Budi Field Crew',
            'email' => 'crew@eventstock.test',
            'password' => bcrypt('password'),
            'role' => 'field_crew',
        ]);

        // 2. Kategori yang bervariasi
        $categories = [
            'Audio Speakers' => 'asset',
            'Microphones' => 'asset',
            'Mixers & Signal Processors' => 'asset',
            'Moving Lights' => 'asset',
            'LED Stage Lights' => 'asset',
            'Rigging Truss' => 'asset',
            'Power & Signal Cables' => 'asset',
            'HDMI & Video Cables' => 'asset',
            'Special Effects (Fog/Co2)' => 'asset',
            'Batteries & Tapes' => 'consumable',
            'Connector Adapters' => 'consumable',
            'Cable Ties & Tape Gaffer' => 'consumable',
        ];

        $categoryModels = [];
        foreach ($categories as $name => $type) {
            $categoryModels[$name] = Category::firstOrCreate(['name' => $name], ['type' => $type]);
        }
        $this->command->info('✅ Kategori siap.');

        // 3. Flightcases
        $flightcases = [];
        $fcData = [
            ['code' => 'FC-L-AUD-01', 'name' => 'Large Audio Rack 01', 'description' => 'Rack case untuk wireless receiver dan power management.'],
            ['code' => 'FC-L-AUD-02', 'name' => 'Large Audio Rack 02', 'description' => 'Rack case untuk wireless receiver dan power management.'],
            ['code' => 'FC-M-MIC-01', 'name' => 'Mic Case Shure A', 'description' => 'Isi Shure SM58 & Beta 58.'],
            ['code' => 'FC-M-MIC-02', 'name' => 'Mic Case Shure B', 'description' => 'Isi Sennheiser wireless handhels.'],
            ['code' => 'FC-XL-CBL-01', 'name' => 'Cable Trunk XLR Large', 'description' => 'Kabel XLR microfon 10m & 20m.'],
            ['code' => 'FC-XL-CBL-02', 'name' => 'Cable Trunk Powercon Large', 'description' => 'Kabel Powercon untuk lighting.'],
            ['code' => 'FC-M-LGT-01', 'name' => 'Martin Aura Case 01', 'description' => 'Flightcase isi 4 unit Martin MAC Aura.'],
            ['code' => 'FC-M-LGT-02', 'name' => 'Martin Aura Case 02', 'description' => 'Flightcase isi 4 unit Martin MAC Aura.'],
            ['code' => 'FC-L-RIG-01', 'name' => 'Rigging Truss Spares Box', 'description' => 'Baut, clamps, safety wire, and shackles.'],
            ['code' => 'FC-M-FX-01', 'name' => 'Stage Fog/Smoke Kit', 'description' => 'Hazer fluid & remote accessories.'],
        ];
        foreach ($fcData as $fc) {
            $flightcases[] = Flightcase::firstOrCreate(['code' => $fc['code']], $fc);
        }
        $this->command->info('✅ 10 Flightcases siap.');

        // 4. Generate 110 Items
        $brands = ['Yamaha', 'Behringer', 'Shure', 'Sennheiser', 'Martin', 'Robe', 'Chauvet', 'Proel', 'Neutrik', 'Klotz'];
        $itemNames = [
            'Audio Speakers' => ['DBR15 Active Speaker', 'DXR12 Powered Speaker', 'HS8 Studio Monitor', 'Subwoofer DXS18', 'Line Array Module T10'],
            'Microphones' => ['SM58 Dynamic Vocal Microphone', 'SM57 Instrument Mic', 'Beta 58A Premium Vocal Mic', 'EW 100 G4 Wireless Mic', 'Beta 91A Kick Drum Mic'],
            'Mixers & Signal Processors' => ['X32 Digital Console', 'TF5 32-Channel Mixer', 'M32R Compact Live Mixer', 'DriveRack PA2 Speaker Management', 'Di-Box Active DI100'],
            'Moving Lights' => ['MAC Aura XB Wash', 'Robe Pointe Beam', 'MAC Viper Profile', 'Chauvet Maverick MK2', 'Sharpy Beam Light 7R'],
            'LED Stage Lights' => ['LED Par 54x3W RGBW', 'LED Bar 18x15W IP65', 'LED Strobe Light 1000W', 'COB Blinder 2x100W', 'LED City Color Wash'],
            'Rigging Truss' => ['Aluminium Truss 290mm 3M', 'Aluminium Truss 290mm 2M', 'Truss Corner Block 3-Way', 'Baseplate Heavy 80x80cm', 'Chain Block 1 Ton 10M'],
            'Power & Signal Cables' => ['XLR Cable Neutrik 10M', 'XLR Cable Neutrik 20M', 'XLR Cable Neutrik 5M', 'Snake Cable 16-Ch 30M', 'Powercon Link Cable 3M'],
            'HDMI & Video Cables' => ['HDMI Cable 4K 15M', 'HDMI Cable 4K 10M', 'SDI Video Cable 30M', 'CAT6 Ethercon Cable 50M', 'DVI to HDMI Converter Cable'],
            'Special Effects (Fog/Co2)' => ['Stage Hazer Machine 1500W', 'COB LED Fogger Vertical', 'Sparkular Cold Spark Machine', 'Heavy Fog Low Smoke Machine', 'Co2 Jet DMX Launcher'],
            'Batteries & Tapes' => ['AA Duracell Alkaline 12-pack', 'AAA Duracell Alkaline 12-pack', '9V Energizer Battery', 'Electrical PVC Tape Nitto', 'Neon Marking Tape 25mm'],
            'Connector Adapters' => ['XLR Male to Jack Adapter', 'XLR Female to RCA Adapter', 'Speakon NL4 Connector', 'Powercon Blue Input Plug', 'HDMI Joiner Coupler'],
            'Cable Ties & Tape Gaffer' => ['Gaffer Tape Black 50mm x 50M', 'Gaffer Tape Silver 50mm x 50M', 'Heavy Cable Ties 300mm 100-pack', 'Velcro Cable Straps 10-pack', 'Bungee Cord Ties with Toggle'],
        ];

        $items = [];
        $skuCounters = [];

        foreach ($categoryModels as $catName => $cat) {
            $names = $itemNames[$catName];
            foreach ($names as $name) {
                // Buat variasi brand
                foreach ($brands as $brand) {
                    $fullName = $brand . ' ' . $name;
                    $prefix = strtoupper(substr($brand, 0, 3) . '-' . substr($catName, 0, 3));
                    if (!isset($skuCounters[$prefix])) {
                        $skuCounters[$prefix] = 1;
                    }
                    $sku = $prefix . '-' . str_pad($skuCounters[$prefix]++, 3, '0', STR_PAD_LEFT);
                    
                    // Jumlah stok acak untuk simulasi gudang besar
                    $totalQty = $cat->type === 'consumable' ? rand(100, 300) : rand(10, 50);
                    
                    $items[] = Item::create([
                        'category_id' => $cat->id,
                        'name' => $fullName,
                        'sku' => $sku,
                        'total_qty' => $totalQty,
                        'available_qty' => $totalQty,
                        'status' => 'available',
                    ]);
                }
            }
        }
        $this->command->info('✅ Berhasil membuat ' . count($items) . ' items.');

        // 5. Asosiasi item ke Flightcases (pivot flightcase_item)
        foreach ($flightcases as $fc) {
            // Pilih 3-5 item random untuk dimasukkan ke flightcase
            $randomItems = array_slice($items, rand(0, count($items) - 10), rand(3, 5));
            foreach ($randomItems as $rItem) {
                if ($rItem->category->type === 'asset') {
                    DB::table('flightcase_item')->insert([
                        'flightcase_id' => $fc->id,
                        'item_id' => $rItem->id,
                        'qty' => rand(2, 6),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        $this->command->info('✅ Item berhasil dibundling ke Flightcases.');

        // 6. Buat 15 Events
        $venues = ['JIEXPO Kemayoran, Jakarta', 'GBK Main Stadium, Jakarta', 'ICE BSD, Tangerang', 'Sentul International Convention Center', 'Manahan Stadium, Solo', 'Garuda Wisnu Kencana, Bali', 'Jatim Park 3 Exhibition Hall', 'Trans Studio Mall Bandung'];
        $eventNames = [
            'Konser Dewa 19 & Orkestra',
            'Synchronize Festival 2026',
            'Pesta Pora Music Festival',
            'GIIAS Automotive Expo 2026',
            'Tech Innovation Summit Asia',
            'National Wedding Fair 2026',
            'Indonesian Esports League Finals',
            'Anual Corporate Gala Dinner Bank Mandiri',
            'Djakarta Warehouse Project 2026',
            'Java Jazz Warm Up Show',
            'New Year Eve Celebration Monas',
            'Gathering Akbar Astra International',
            'Anime Festival Asia Indonesia',
            'Soundrenaline Roadshow Bali',
            'Charity Concert Save Children',
        ];

        $events = [];
        $startDate = now()->subDays(200); // Mulai dari 200 hari yang lalu agar ada riwayat lengkap (6+ bulan)

        foreach ($eventNames as $index => $eName) {
            $eventStart = clone $startDate;
            $eventStart->addDays($index * 5); // menyebar
            $eventEnd = (clone $eventStart)->addDays(rand(1, 3));

            $status = 'upcoming';
            if ($eventEnd < now()) {
                $status = 'completed';
            } elseif ($eventStart <= now() && $eventEnd >= now()) {
                $status = 'ongoing';
            }

            $events[] = Event::create([
                'name' => $eName,
                'venue' => $venues[array_rand($venues)],
                'start_date' => $eventStart,
                'end_date' => $eventEnd,
                'status' => $status,
            ]);
        }
        $this->command->info('✅ 15 Events siap.');

        // 7. Generate 60+ Transaksi Manifests & Logs (Outbound, Inbound, Stock Adjustment, Incident)
        $this->command->warn('Membuat 60+ transaksi manifest dan riwayat stok...');

        $outboundCount = 0;
        $inboundCount = 0;
        $adjustmentCount = 0;

        foreach ($events as $event) {
            // A. OUTBOUND TRANSACTION
            $manifestNumber = 'OUT-' . $event->start_date->format('Ymd') . '-' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
            $outboundStatus = $event->status === 'completed' ? 'completed' : 'in_progress';
            
            $outbound = Manifest::create([
                'manifest_number' => $manifestNumber,
                'event_id' => $event->id,
                'user_id' => rand(0, 1) ? $crew->id : $manager->id,
                'type' => 'outbound',
                'status' => $outboundStatus,
                'notes' => 'Pengiriman barang logistik utama untuk event ' . $event->name,
                'destination' => $event->venue,
                'created_at' => $event->start_date->subDays(1),
                'updated_at' => $event->start_date->subDays(1),
            ]);
            $outboundCount++;

            // Pilih 6-12 items untuk outbound ini
            $manifestedItems = [];
            $selectedItems = (array) array_rand($items, rand(6, 12));
            
            foreach ($selectedItems as $itemKey) {
                $item = $items[$itemKey];
                $qty = rand(1, 5);
                
                // Pastikan qty tidak melebihi stok
                if ($item->available_qty >= $qty) {
                    ManifestItem::create([
                        'manifest_id' => $outbound->id,
                        'item_id' => $item->id,
                        'qty_requested' => $qty,
                        'qty_actual' => $qty,
                        'condition' => 'good',
                    ]);

                    // Catat Stock Transaction
                    StockTransaction::create([
                        'item_id' => $item->id,
                        'user_id' => $outbound->user_id,
                        'type' => 'out',
                        'qty_change' => -$qty,
                        'qty_before' => $item->available_qty,
                        'qty_after' => $item->available_qty - $qty,
                        'description' => 'Outbound Manifest ' . $outbound->manifest_number . ' for ' . $event->name,
                        'created_at' => $outbound->created_at,
                        'updated_at' => $outbound->updated_at,
                    ]);

                    $item->available_qty -= $qty;
                    if ($item->category->type === 'consumable') {
                        $item->total_qty -= $qty; // Consumable habis dipakai
                    } else {
                        $item->status = 'on_duty';
                    }
                    $item->save();

                    // Simpan untuk inbound nanti
                    $manifestedItems[] = [
                        'item' => $item,
                        'qty' => $qty
                    ];
                }
            }

            // B. INBOUND TRANSACTION (Hanya jika event sudah selesai)
            if ($event->status === 'completed' && !empty($manifestedItems)) {
                $inboundManifestNumber = 'INB-' . $event->end_date->format('Ymd') . '-' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
                $inbound = Manifest::create([
                    'manifest_number' => $inboundManifestNumber,
                    'event_id' => $event->id,
                    'user_id' => $manager->id,
                    'type' => 'inbound',
                    'status' => 'completed',
                    'notes' => 'Pengembalian barang dan check in gudang pasca event ' . $event->name,
                    'destination' => 'Gudang Pusat Cengkareng',
                    'outbound_manifest_id' => $outbound->id,
                    'created_at' => $event->end_date->addHours(4),
                    'updated_at' => $event->end_date->addHours(4),
                ]);
                $inboundCount++;

                $hasIssue = false;

                foreach ($manifestedItems as $mi) {
                    $item = $mi['item'];
                    $qtyOut = $mi['qty'];

                    // Simulasi kondisi pengembalian (92% Good, 5% Broken, 3% Lost)
                    $randCondition = rand(1, 100);
                    $condition = 'good';
                    $actualQty = $qtyOut;
                    $incidentNote = '';

                    if ($randCondition > 95 && $item->category->type === 'asset') {
                        // Lost
                        $condition = 'lost';
                        $actualQty = 0; // Hilang semua
                        $incidentNote = 'Hilang saat loading out di venue.';
                        $hasIssue = true;
                    } elseif ($randCondition > 90 && $item->category->type === 'asset') {
                        // Broken
                        $condition = 'broken';
                        $incidentNote = 'Body retak/korsleting listrik akibat hujan.';
                        $hasIssue = true;
                    }

                    $mItem = ManifestItem::create([
                        'manifest_id' => $inbound->id,
                        'item_id' => $item->id,
                        'qty_requested' => 0,
                        'qty_actual' => $actualQty,
                        'condition' => $condition,
                        'notes' => $incidentNote ?: null,
                    ]);

                    if ($condition === 'good') {
                        // Kembali normal ke stock
                        StockTransaction::create([
                            'item_id' => $item->id,
                            'user_id' => $inbound->user_id,
                            'type' => 'in',
                            'qty_change' => $actualQty,
                            'qty_before' => $item->available_qty,
                            'qty_after' => $item->available_qty + $actualQty,
                            'description' => 'Inbound Manifest ' . $inbound->manifest_number . ' (Kondisi: Baik)',
                            'created_at' => $inbound->created_at,
                            'updated_at' => $inbound->updated_at,
                        ]);

                        $item->available_qty += $actualQty;
                        $item->status = 'available';
                        $item->save();
                    } elseif ($condition === 'broken') {
                        // Masuk ke repair/maintenance
                        StockTransaction::create([
                            'item_id' => $item->id,
                            'user_id' => $inbound->user_id,
                            'type' => 'correction',
                            'qty_change' => 0,
                            'qty_before' => $item->available_qty,
                            'qty_after' => $item->available_qty,
                            'description' => 'Inbound Manifest ' . $inbound->manifest_number . ' (Kondisi: Rusak)',
                            'created_at' => $inbound->created_at,
                            'updated_at' => $inbound->updated_at,
                        ]);

                        $item->status = 'maintenance';
                        $item->save();

                        // Catat Incident Log
                        IncidentLog::create([
                            'manifest_item_id' => $mItem->id,
                            'type' => 'broken',
                            'qty_affected' => $qtyOut,
                            'resolved' => false,
                        ]);
                    } elseif ($condition === 'lost') {
                        // Dikurangi dari total stock
                        StockTransaction::create([
                            'item_id' => $item->id,
                            'user_id' => $inbound->user_id,
                            'type' => 'correction',
                            'qty_change' => -$qtyOut,
                            'qty_before' => $item->available_qty,
                            'qty_after' => $item->available_qty,
                            'description' => 'Inbound Manifest ' . $inbound->manifest_number . ' (Kondisi: Hilang)',
                            'created_at' => $inbound->created_at,
                            'updated_at' => $inbound->updated_at,
                        ]);

                        $item->total_qty -= $qtyOut;
                        $item->status = 'lost';
                        $item->save();

                        // Catat Incident Log
                        IncidentLog::create([
                            'manifest_item_id' => $mItem->id,
                            'type' => 'lost',
                            'qty_affected' => $qtyOut,
                            'resolved' => false,
                        ]);
                    }
                }

                if ($hasIssue) {
                    $outbound->update(['status' => 'has_issue']);
                }
            }
        }

        // C. STOCK ADJUSTMENT MANUAL / STOCK OPNAME TRANSACTIONS (15+ transactions)
        for ($i = 0; $i < 15; $i++) {
            $randomItem = $items[array_rand($items)];
            $adjQty = rand(10, 100);
            
            StockTransaction::create([
                'item_id' => $randomItem->id,
                'user_id' => $manager->id,
                'type' => 'adjustment',
                'qty_change' => $adjQty - $randomItem->available_qty,
                'qty_before' => $randomItem->available_qty,
                'qty_after' => $adjQty,
                'description' => 'Stock opname rutin bulanan. Penyelarasan stock fisik.',
                'created_at' => now()->subDays(rand(1, 30)),
            ]);

            $randomItem->available_qty = $adjQty;
            $randomItem->total_qty = $adjQty;
            $randomItem->save();
            $adjustmentCount++;
        }

        // D. TRANSAKSI TAMBAH STOK BARU (RESTOCK) (10+ transactions)
        for ($i = 0; $i < 10; $i++) {
            $randomItem = $items[array_rand($items)];
            $addQty = rand(20, 50);

            StockTransaction::create([
                'item_id' => $randomItem->id,
                'user_id' => $manager->id,
                'type' => 'in',
                'qty_change' => $addQty,
                'qty_before' => $randomItem->available_qty,
                'qty_after' => $randomItem->available_qty + $addQty,
                'description' => 'Pembelian Restock Baru dari Supplier Utama.',
                'created_at' => now()->subDays(rand(1, 15)),
            ]);

            $randomItem->available_qty += $addQty;
            $randomItem->total_qty += $addQty;
            $randomItem->save();
            $inboundCount++;
        }

        // E. Selesaikan beberapa incident agar data history bervariasi
        $unresolvedIncidents = IncidentLog::where('resolved', false)->get();
        foreach ($unresolvedIncidents->take(3) as $incident) {
            $incident->update([
                'resolved' => true,
            ]);
            // Balikkan status item ke available
            $incident->manifestItem->item->update([
                'status' => 'available'
            ]);
        }

        $this->command->info('========================================================');
        $this->command->info(' 🎯 SIMULASI SEEDING GUDANG BESAR SELESAI!');
        $this->command->info('========================================================');
        $this->command->info('  Kategori Baru       : ' . count($categories));
        $this->command->info('  Total Flightcases   : 10');
        $this->command->info('  Total Items Dibuat  : ' . count($items));
        $this->command->info('  Total Events        : 15');
        $this->command->info('  Outbound Manifests  : ' . $outboundCount);
        $this->command->info('  Inbound Manifests   : ' . $inboundCount);
        $this->command->info('  Manual Adjustments  : ' . $adjustmentCount);
        $this->command->info('========================================================');
    }
}
