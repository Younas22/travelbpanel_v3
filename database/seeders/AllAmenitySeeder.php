<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AllAmenity;

class AllAmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            ['name' => 'WiFi', 'icon' => 'bi bi-wifi'],
            ['name' => 'Parking', 'icon' => 'bi bi-car-front'],
            ['name' => 'Air Conditioning', 'icon' => 'bi bi-snow'],
            ['name' => 'TV', 'icon' => 'bi bi-tv'],
            ['name' => 'Swimming Pool', 'icon' => 'bi bi-water'],
            ['name' => 'Gym', 'icon' => 'bi bi-bicycle'],
            ['name' => 'Restaurant', 'icon' => 'bi bi-shop'],
            ['name' => 'Room Service', 'icon' => 'bi bi-bell'],
            ['name' => 'Laundry', 'icon' => 'bi bi-droplet'],
            ['name' => 'Elevator', 'icon' => 'bi bi-arrow-up-square'],
            ['name' => 'Bar', 'icon' => 'bi bi-cup-straw'],
            ['name' => 'Spa', 'icon' => 'bi bi-heart-pulse'],
            ['name' => 'Conference Room', 'icon' => 'bi bi-people'],
            ['name' => '24/7 Reception', 'icon' => 'bi bi-clock'],
            ['name' => 'Breakfast Included', 'icon' => 'bi bi-egg-fried'],
            ['name' => 'Mini Bar', 'icon' => 'bi bi-cup-hot'],
            ['name' => 'Safe', 'icon' => 'bi bi-shield-lock'],
            ['name' => 'Balcony', 'icon' => 'bi bi-door-open'],
            ['name' => 'Airport Shuttle', 'icon' => 'bi bi-bus-front'],
            ['name' => 'Concierge', 'icon' => 'bi bi-person-badge'],
        ];

        foreach ($amenities as $amenity) {
            AllAmenity::firstOrCreate(
                ['name' => $amenity['name']],
                $amenity
            );
        }

        echo "✅ Created " . count($amenities) . " amenities\n";
    }
}
