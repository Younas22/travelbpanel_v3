<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RoomType;
use App\Models\AllAmenity;

class RoomTypeAmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roomTypes = RoomType::with('hotel')->get();

        // Cache room-appropriate amenities
        $amenities = AllAmenity::all()->keyBy('name');

        // Define room amenity tiers
        $basicRoomAmenities = [
            $amenities['WiFi']->id,
            $amenities['Air Conditioning']->id,
            $amenities['TV']->id,
        ];

        $standardRoomAmenities = [
            $amenities['Safe']->id,
            $amenities['Mini Bar']->id,
            $amenities['Room Service']->id,
            $amenities['Laundry']->id,
        ];

        $premiumRoomAmenities = [
            $amenities['Balcony']->id,
            $amenities['Breakfast Included']->id,
        ];

        foreach ($roomTypes as $roomType) {
            $amenityIds = [];
            $roomName = strtolower($roomType->name);

            if (str_contains($roomName, 'standard')) {
                // 3-5 amenities: basic + 0-2 standard
                $amenityIds = array_merge(
                    $basicRoomAmenities,
                    array_slice($standardRoomAmenities, 0, rand(0, 2))
                );
            } elseif (str_contains($roomName, 'deluxe') || str_contains($roomName, 'family')) {
                // 6-8 amenities: basic + most standard + 0-1 premium
                $amenityIds = array_merge(
                    $basicRoomAmenities,
                    array_slice($standardRoomAmenities, 0, rand(2, 4)),
                    array_slice($premiumRoomAmenities, 0, rand(0, 1))
                );
            } else { // Suite, Executive, Presidential
                // 8-10 amenities: all basic + all standard + all premium
                $amenityIds = array_merge(
                    $basicRoomAmenities,
                    $standardRoomAmenities,
                    $premiumRoomAmenities
                );
            }

            // Remove duplicates
            $amenityIds = array_unique($amenityIds);

            $roomType->amenities()->attach($amenityIds);
            echo "Attached " . count($amenityIds) . " amenities to {$roomType->name} at {$roomType->hotel->name}\n";
        }

        echo "\n✅ Successfully attached amenities to all room types!\n";
    }
}
