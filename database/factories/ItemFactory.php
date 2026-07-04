<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Daftar prefix SKU untuk AVL
        $prefixes = ['AUD', 'VIS', 'LIG', 'CBL', 'ACC'];
        $prefix = $this->faker->randomElement($prefixes);

        // Alat-alat AVL dummy
        $audio = ['Microphone Shure SM58', 'Microphone Sennheiser EW100', 'Speaker Active Yamaha', 'Mixer Behringer X32', 'DI Box BSS'];
        $visual = ['Projector Epson 10K', 'LED Screen P3.9', 'Video Switcher Roland', 'Kamera Sony A7S', 'Tripod Libec'];
        $lighting = ['Moving Head Beam 230', 'Par LED RGBW', 'Fresnel 1000W', 'Lighting Console Avolite', 'Smoke Machine'];
        $cables = ['Kabel XLR 10m', 'Kabel XLR 5m', 'Kabel SDI 50m', 'Kabel Powercon 3m', 'Kabel HDMI 15m'];

        $allItems = array_merge($audio, $visual, $lighting, $cables);

        $totalQty = $this->faker->numberBetween(1, 50);

        // Randomize status
        $statuses = ['available', 'on_duty', 'maintenance', 'lost'];
        $status = $this->faker->randomElement($statuses);

        // Jika available, set stok tersedia sama dengan total. Jika tidak, kurangi secara acak.
        $availableQty = $status === 'available' ? $totalQty : $this->faker->numberBetween(0, $totalQty - 1);

        return [
            'sku' => $prefix . '-' . $this->faker->unique()->numerify('####'),
            'name' => $this->faker->randomElement($allItems) . ' ' . $this->faker->word(),
            'category_id' => $this->faker->numberBetween(1, 4), // Asumsi ID Kategori 1 s/d 4
            'total_qty' => $totalQty,
            'available_qty' => $availableQty,
            'status' => $status,
        ];
    }
}
