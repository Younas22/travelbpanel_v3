<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotels = Hotel::with('roomTypes')->get();
        $totalRooms = 0;

        foreach ($hotels as $hotel) {
            $roomTypes = $hotel->roomTypes->sortBy('price_per_night'); // Sort by price (cheapest first)
            $totalRoomsForHotel = $hotel->total_rooms;

            if ($roomTypes->isEmpty()) {
                echo "Skipping {$hotel->name} - no room types defined\n";
                continue;
            }

            // Calculate room distribution
            $distribution = $this->calculateRoomDistribution($roomTypes->count(), $totalRoomsForHotel);

            $roomNumber = 101; // Start from room 101
            $currentFloor = 1;
            $roomsPerFloor = 10; // Standard 10 rooms per floor

            foreach ($roomTypes as $index => $roomType) {
                $roomCount = $distribution[$index];

                for ($i = 0; $i < $roomCount; $i++) {
                    // Determine floor
                    if (($roomNumber % 100) > $roomsPerFloor) {
                        $currentFloor++;
                        $roomNumber = ($currentFloor * 100) + 1;
                    }

                    // Floor name
                    $floorName = match($currentFloor) {
                        1 => 'Ground Floor',
                        2 => '1st Floor',
                        3 => '2nd Floor',
                        4 => '3rd Floor',
                        default => ($currentFloor - 1) . 'th Floor',
                    };

                    // Status with weighted random
                    $rand = rand(1, 100);
                    $status = match(true) {
                        $rand <= 70 => 'available',
                        $rand <= 90 => 'occupied',
                        default => 'maintenance',
                    };

                    Room::create([
                        'hotel_id' => $hotel->id,
                        'room_type_id' => $roomType->id,
                        'room_number' => (string)$roomNumber,
                        'floor' => $floorName,
                        'status' => $status,
                    ]);

                    $roomNumber++;
                    $totalRooms++;
                }
            }

            echo "Created {$totalRoomsForHotel} rooms for {$hotel->name} across {$currentFloor} floors\n";
        }

        echo "\n✅ Successfully created {$totalRooms} rooms!\n";
    }

    /**
     * Calculate room distribution across room types
     */
    private function calculateRoomDistribution($roomTypeCount, $totalRooms)
    {
        if ($roomTypeCount === 1) {
            return [$totalRooms];
        }

        // Pyramid distribution weights
        $weights = match($roomTypeCount) {
            2 => [0.60, 0.40], // 60% standard, 40% deluxe
            3 => [0.50, 0.30, 0.20], // 50% standard, 30% deluxe, 20% suite
            4 => [0.50, 0.30, 0.15, 0.05], // Pyramid distribution
            default => array_fill(0, $roomTypeCount, 1 / $roomTypeCount), // Equal distribution fallback
        };

        $distribution = [];
        $remaining = $totalRooms;

        for ($i = 0; $i < $roomTypeCount - 1; $i++) {
            $count = (int)floor($totalRooms * $weights[$i]);
            $distribution[] = $count;
            $remaining -= $count;
        }

        // Assign remaining to last type
        $distribution[] = $remaining;

        return $distribution;
    }
}
