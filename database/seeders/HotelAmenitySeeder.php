<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\AllAmenity;

class HotelAmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotels = Hotel::all();

        // Cache amenity IDs by name for easy reference
        $amenities = AllAmenity::all()->keyBy('name');

        // Define amenity tiers
        $commonAmenities = [
            $amenities['WiFi']->id,
            $amenities['Parking']->id,
            $amenities['24/7 Reception']->id,
        ];

        $standardAmenities = [
            $amenities['Air Conditioning']->id,
            $amenities['TV']->id,
            $amenities['Elevator']->id,
            $amenities['Laundry']->id,
            $amenities['Room Service']->id,
            $amenities['Restaurant']->id,
            $amenities['Breakfast Included']->id,
        ];

        $premiumAmenities = [
            $amenities['Swimming Pool']->id,
            $amenities['Gym']->id,
            $amenities['Spa']->id,
            $amenities['Bar']->id,
            $amenities['Conference Room']->id,
            $amenities['Concierge']->id,
            $amenities['Airport Shuttle']->id,
        ];

        foreach ($hotels as $hotel) {
            $amenityIds = [];

            if ($hotel->type === 'guest house') {
                // 4-6 amenities: all common + 1-3 standard
                $amenityIds = array_merge(
                    $commonAmenities,
                    array_slice($standardAmenities, 0, rand(1, 3))
                );
            } elseif ($hotel->type === 'hotel') {
                // 7-10 amenities: all common + most standard + 1-2 premium
                $amenityIds = array_merge(
                    $commonAmenities,
                    array_slice($standardAmenities, 0, rand(4, 6)),
                    array_slice($premiumAmenities, 0, rand(1, 2))
                );
            } else { // resort
                // 12-15 amenities: all common + all standard + most premium
                $amenityIds = array_merge(
                    $commonAmenities,
                    $standardAmenities,
                    array_slice($premiumAmenities, 0, rand(5, 7))
                );
            }

            $hotel->amenities()->attach($amenityIds);
            echo "Attached " . count($amenityIds) . " amenities to {$hotel->name}\n";
        }

        echo "\n✅ Successfully attached amenities to all hotels!\n";
    }
}
