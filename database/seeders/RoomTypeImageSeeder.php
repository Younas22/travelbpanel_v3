<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RoomType;
use App\Models\RoomTypeImage;

class RoomTypeImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roomTypes = RoomType::all();
        $totalImages = 0;

        foreach ($roomTypes as $roomType) {
            // Determine image count based on room type name (tier)
            $imageCount = match(true) {
                str_contains(strtolower($roomType->name), 'presidential') => 4,
                str_contains(strtolower($roomType->name), 'suite') ||
                str_contains(strtolower($roomType->name), 'executive') => 3,
                str_contains(strtolower($roomType->name), 'deluxe') => 3,
                default => 2, // Standard
            };

            for ($i = 1; $i <= $imageCount; $i++) {
                RoomTypeImage::create([
                    'room_type_id' => $roomType->id,
                    'image_path' => "hotel-rooms/room_type_{$roomType->id}_img{$i}.jpg",
                ]);
                $totalImages++;
            }

            echo "Created {$imageCount} images for room type: {$roomType->name} (ID: {$roomType->id})\n";
        }

        echo "\n✅ Successfully created {$totalImages} room type images!\n";
    }
}
