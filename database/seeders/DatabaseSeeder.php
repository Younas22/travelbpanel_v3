<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        echo "\n🏨 Starting Hotel Management System Seeding...\n\n";

        // 1. Reference data (amenities)
        echo "1. Seeding amenities...\n";
        $this->call(AllAmenitySeeder::class);

        // 2. Core entities (hotels)
        echo "\n2. Seeding hotels...\n";
        $this->call(HotelSeeder::class);

        // 3. Hotel-related data
        echo "\n3. Seeding hotel images...\n";
        $this->call(HotelImageSeeder::class);

        echo "\n4. Attaching hotel amenities...\n";
        $this->call(HotelAmenitySeeder::class);

        echo "\n5. Seeding hotel policies...\n";
        $this->call(HotelPolicySeeder::class);

        // 4. Room types
        echo "\n6. Seeding room types...\n";
        $this->call(RoomTypeSeeder::class);

        // 5. Room type data
        echo "\n7. Seeding room type images...\n";
        $this->call(RoomTypeImageSeeder::class);

        echo "\n8. Attaching room type amenities...\n";
        $this->call(RoomTypeAmenitySeeder::class);

        // 6. Individual rooms
        echo "\n9. Seeding rooms...\n";
        $this->call(RoomSeeder::class);

        echo "\n✅ Hotel Management System seeding completed!\n";
    }
}
