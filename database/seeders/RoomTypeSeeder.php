<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\RoomType;

class RoomTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotels = Hotel::all();
        $totalRoomTypes = 0;

        // Define room type templates
        $roomTypeTemplates = [
            'guest house' => [
                [
                    'name' => 'Standard Room',
                    'description' => 'Cozy and comfortable standard room perfect for budget-conscious travelers. Features essential amenities including a comfortable bed, clean private bathroom, basic furniture, and a peaceful ambiance. Ideal for solo travelers or couples looking for a simple yet pleasant stay.',
                    'price_range' => [3000, 4000],
                    'beds' => 1,
                    'max_adults' => 2,
                    'max_children' => 1,
                    'ac' => false,
                ],
                [
                    'name' => 'Deluxe Room',
                    'description' => 'Spacious deluxe room with enhanced comfort and modern amenities. Includes air conditioning, premium bedding, work desk, comfortable seating area, and upgraded bathroom fixtures. Perfect for those seeking extra comfort without luxury pricing.',
                    'price_range' => [5000, 6000],
                    'beds' => 2,
                    'max_adults' => 3,
                    'max_children' => 2,
                    'ac' => true,
                ],
                [
                    'name' => 'Family Room',
                    'description' => 'Generously sized family room designed for comfort and convenience. Features multiple beds, air conditioning, ample storage space, and a larger bathroom. Ideal for families with children or groups of friends traveling together.',
                    'price_range' => [7000, 8000],
                    'beds' => 3,
                    'max_adults' => 4,
                    'max_children' => 2,
                    'ac' => true,
                ],
            ],
            'hotel' => [
                [
                    'name' => 'Standard Room',
                    'description' => 'Well-appointed standard room featuring modern decor and essential business amenities. Includes air conditioning, flat-screen TV, work desk with ergonomic chair, mini fridge, tea/coffee maker, and complimentary WiFi. Perfect for business travelers and tourists.',
                    'price_range' => [5000, 7000],
                    'beds' => 1,
                    'max_adults' => 2,
                    'max_children' => 1,
                    'ac' => true,
                ],
                [
                    'name' => 'Deluxe Room',
                    'description' => 'Elegant deluxe room with superior comfort and upgraded amenities. Features premium bedding, larger workspace, mini bar, safe deposit box, bathrobes, enhanced bathroom with premium toiletries, and choice of city or garden views. Ideal for extended stays.',
                    'price_range' => [8000, 12000],
                    'beds' => 2,
                    'max_adults' => 3,
                    'max_children' => 2,
                    'ac' => true,
                ],
                [
                    'name' => 'Executive Room',
                    'description' => 'Premium executive room designed for business professionals. Includes spacious work area, high-speed internet, separate sitting area, executive lounge access, complimentary breakfast, premium minibar, and enhanced business services. Perfect for corporate travelers.',
                    'price_range' => [13000, 18000],
                    'beds' => 2,
                    'max_adults' => 3,
                    'max_children' => 2,
                    'ac' => true,
                ],
                [
                    'name' => 'Suite',
                    'description' => 'Luxurious suite with separate bedroom and living area. Features king-size bed, spacious bathroom with bathtub, large work desk, dining area, premium entertainment system, minibar, and panoramic views. Includes butler service and exclusive amenities for a memorable stay.',
                    'price_range' => [20000, 30000],
                    'beds' => 2,
                    'max_adults' => 4,
                    'max_children' => 2,
                    'ac' => true,
                ],
            ],
            'resort' => [
                [
                    'name' => 'Standard Room',
                    'description' => 'Beautifully designed standard room with resort amenities and scenic views. Features comfortable bedding, modern bathroom, private balcony, air conditioning, minibar, and access to all resort facilities including pool and gym. Perfect for leisure travelers.',
                    'price_range' => [8000, 10000],
                    'beds' => 2,
                    'max_adults' => 2,
                    'max_children' => 1,
                    'ac' => true,
                ],
                [
                    'name' => 'Deluxe Room',
                    'description' => 'Premium deluxe room with enhanced comfort and spectacular views. Includes superior bedding, spacious bathroom with rainfall shower, large balcony, seating area, premium minibar, and upgraded room service. Ideal for romantic getaways and relaxation.',
                    'price_range' => [12000, 18000],
                    'beds' => 2,
                    'max_adults' => 3,
                    'max_children' => 2,
                    'ac' => true,
                ],
                [
                    'name' => 'Executive Suite',
                    'description' => 'Spacious executive suite with separate living and sleeping areas. Features king bed, luxurious bathroom with jacuzzi, expansive private terrace, dining space, premium entertainment, and exclusive concierge services. Perfect for special occasions and extended stays.',
                    'price_range' => [20000, 30000],
                    'beds' => 2,
                    'max_adults' => 4,
                    'max_children' => 2,
                    'ac' => true,
                ],
                [
                    'name' => 'Presidential Suite',
                    'description' => 'Ultimate luxury presidential suite with panoramic views and world-class amenities. Includes master bedroom, guest bedroom, private living room, full dining area, kitchen, premium bathrooms with spa features, private butler, and access to exclusive resort areas. The epitome of luxury.',
                    'price_range' => [40000, 60000],
                    'beds' => 3,
                    'max_adults' => 6,
                    'max_children' => 3,
                    'ac' => true,
                ],
            ],
        ];

        foreach ($hotels as $hotel) {
            // Get templates for this hotel type
            $templates = $roomTypeTemplates[$hotel->type] ?? $roomTypeTemplates['hotel'];

            // Determine room type count
            $roomTypeCount = match($hotel->type) {
                'guest house' => rand(2, 3),
                'hotel' => rand(3, 4),
                'resort' => 4,
                default => 3,
            };

            for ($i = 0; $i < $roomTypeCount; $i++) {
                $template = $templates[$i];

                $roomType = RoomType::create([
                    'hotel_id' => $hotel->id,
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'price_per_night' => rand($template['price_range'][0], $template['price_range'][1]),
                    'beds' => $template['beds'],
                    'max_adults' => $template['max_adults'],
                    'max_children' => $template['max_children'],
                    'ac' => $template['ac'],
                    'status' => rand(0, 100) < 95 ? 1 : 0, // 95% active
                ]);

                echo "Created room type: {$roomType->name} for {$hotel->name} - PKR {$roomType->price_per_night}/night\n";
                $totalRoomTypes++;
            }
        }

        echo "\n✅ Successfully created {$totalRoomTypes} room types!\n";
    }
}
