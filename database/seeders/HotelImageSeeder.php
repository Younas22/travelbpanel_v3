<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\HotelImage;

class HotelImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotels = Hotel::all();
        $totalImages = 0;

        foreach ($hotels as $hotel) {
            // Determine image count based on hotel type
            $imageCount = match($hotel->type) {
                'resort' => 5,
                'hotel' => 4,
                'guest house' => 3,
                default => 3,
            };

            $imageTypes = ['front', 'lobby', 'parking', 'pool', 'restaurant', 'general'];

            for ($i = 0; $i < $imageCount; $i++) {
                HotelImage::create([
                    'hotel_id' => $hotel->id,
                    'image_path' => "hotel/hotel_{$hotel->id}_{$imageTypes[$i]}.jpg",
                    'image_type' => $imageTypes[$i],
                    'sort_order' => $i,
                ]);
                $totalImages++;
            }

            echo "Created {$imageCount} images for {$hotel->name}\n";
        }

        echo "\n✅ Successfully created {$totalImages} hotel images!\n";
    }
}
