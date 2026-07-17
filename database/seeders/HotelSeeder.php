<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\Location;

class HotelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotels = [
            // Luxury Hotels (6)
            [
                'location_id' => Location::inRandomOrder()->first()->id ?? 1,
                'name' => 'Pearl Continental Karachi',
                'type' => 'hotel',
                'description' => 'Pearl Continental Karachi is a prestigious 5-star luxury hotel located in the heart of Karachi\'s business district. With over 40 years of hospitality excellence, the hotel features elegant rooms with modern amenities, multiple dining options including international and local cuisine, a rooftop swimming pool with panoramic city views, fully-equipped fitness center, and extensive conference facilities. Perfect for business travelers and tourists alike, the hotel offers world-class service with traditional Pakistani hospitality.',
                'address' => 'Club Road, Civil Lines, Karachi, Sindh',
                'phone' => '+92-21-35685660',
                'whatsapp' => '+92-21-35685660',
                'email' => 'info@pckarachi.com',
                'check_in_time' => '14:00:00',
                'check_out_time' => '12:00:00',
                'total_rooms' => 75,
                'status' => 1,
            ],
            [
                'location_id' => Location::inRandomOrder()->first()->id ?? 1,
                'name' => 'Serena Hotel Islamabad',
                'type' => 'hotel',
                'description' => 'Serena Hotel Islamabad is an elegant 5-star establishment situated in the diplomatic enclave of Pakistan\'s capital city. The hotel seamlessly blends contemporary luxury with traditional architectural elements inspired by Gandharan civilization. Featuring spacious rooms with marble bathrooms, multiple award-winning restaurants, an outdoor heated pool, a world-class spa, and state-of-the-art business facilities, Serena Hotel provides an oasis of tranquility for discerning travelers seeking sophistication and comfort in the heart of Islamabad.',
                'address' => 'Khayaban-e-Suharwardy, G-5/1, Islamabad',
                'phone' => '+92-51-2874000',
                'whatsapp' => '+92-51-2874000',
                'email' => 'reservations@serenahotels.com',
                'check_in_time' => '15:00:00',
                'check_out_time' => '12:00:00',
                'total_rooms' => 68,
                'status' => 1,
            ],
            [
                'location_id' => Location::inRandomOrder()->first()->id ?? 1,
                'name' => 'Avari Towers Lahore',
                'type' => 'hotel',
                'description' => 'Avari Towers Lahore stands as one of the most distinguished hotels in the cultural capital of Pakistan. This luxury hotel combines modern facilities with warm Pakistani hospitality, offering elegantly appointed rooms, fine dining experiences showcasing local and international cuisine, a rooftop restaurant with stunning city views, health club with swimming pool, and comprehensive business facilities. Ideally located near key commercial areas and historical sites, it serves both business and leisure travelers with exceptional service and attention to detail.',
                'address' => '87 Shahrah-e-Quaid-e-Azam, The Mall, Lahore, Punjab',
                'phone' => '+92-42-36360360',
                'whatsapp' => '+92-42-36360360',
                'email' => 'contact@avari.com',
                'check_in_time' => '14:00:00',
                'check_out_time' => '11:00:00',
                'total_rooms' => 62,
                'status' => 1,
            ],
            [
                'location_id' => Location::inRandomOrder()->first()->id ?? 1,
                'name' => 'Marriott Executive Apartments Karachi',
                'type' => 'hotel',
                'description' => 'Marriott Executive Apartments Karachi offers sophisticated extended-stay accommodations in the heart of the city\'s business district. Each spacious apartment features a fully-equipped kitchen, separate living and sleeping areas, modern work spaces with high-speed internet, and premium amenities. The property includes a rooftop pool, fitness center, business lounge, and on-site dining options. Designed for both short and long-term stays, it provides the perfect blend of home comfort and hotel luxury, making it ideal for business executives and relocating families.',
                'address' => 'Plot No. ST-2/1, Block-9, KDA Improvement Scheme No. 5, Clifton, Karachi, Sindh',
                'phone' => '+92-21-35706060',
                'whatsapp' => '+92-21-35706060',
                'email' => 'info@marriottkarachi.com',
                'check_in_time' => '15:00:00',
                'check_out_time' => '12:00:00',
                'total_rooms' => 55,
                'status' => 1,
            ],
            [
                'location_id' => Location::inRandomOrder()->first()->id ?? 1,
                'name' => 'Ramada Plaza Karachi',
                'type' => 'hotel',
                'description' => 'Ramada Plaza Karachi is a contemporary business hotel offering modern comfort and convenience in Pakistan\'s commercial hub. The hotel features well-appointed rooms with comfortable bedding, flat-screen TVs, and work desks, along with diverse dining options, a fitness center, swimming pool, and flexible meeting spaces. Strategically located near the airport and key business areas, Ramada Plaza caters to corporate travelers and tourists seeking quality accommodation at competitive rates. The friendly staff ensures a pleasant and productive stay for all guests.',
                'address' => 'Sharah-e-Faisal Road, Stadium Commercial Area, Karachi, Sindh',
                'phone' => '+92-21-34525252',
                'whatsapp' => '+92-21-34525252',
                'email' => 'contact@ramadakarachi.com',
                'check_in_time' => '14:00:00',
                'check_out_time' => '11:00:00',
                'total_rooms' => 48,
                'status' => 1,
            ],
            [
                'location_id' => Location::inRandomOrder()->first()->id ?? 1,
                'name' => 'The Nishat Hotel Lahore',
                'type' => 'hotel',
                'description' => 'The Nishat Hotel Lahore is a premier business hotel located in the heart of Lahore\'s commercial district. Known for its exceptional service and elegant ambiance, the hotel offers spacious rooms with contemporary decor, multiple dining venues serving Pakistani and international cuisine, a well-equipped business center, meeting facilities, and a fitness center. The hotel\'s central location provides easy access to Lahore\'s historical landmarks, shopping districts, and business centers, making it an ideal choice for both corporate and leisure travelers seeking comfort and convenience.',
                'address' => 'Shahrah-e-Quaid-e-Azam, Gulberg III, Lahore, Punjab',
                'phone' => '+92-42-35756000',
                'whatsapp' => '+92-42-35756000',
                'email' => 'reservations@nishathotels.com',
                'check_in_time' => '15:00:00',
                'check_out_time' => '11:00:00',
                'total_rooms' => 58,
                'status' => 1,
            ],

            // Guest Houses (4)
            [
                'location_id' => Location::inRandomOrder()->first()->id ?? 1,
                'name' => 'Green Valley Guest House Murree',
                'type' => 'guest house',
                'description' => 'Nestled in the picturesque hills of Murree, Green Valley Guest House offers a cozy retreat for families and couples seeking peace and natural beauty. The guest house features comfortable rooms with mountain views, home-cooked Pakistani meals, and warm hospitality. Located just minutes from Mall Road, guests can enjoy easy access to local attractions while experiencing the tranquility of the mountains. Ideal for weekend getaways and extended stays, our family-run establishment provides personalized attention and a homely atmosphere that makes every guest feel welcome.',
                'address' => 'Lower Topa Road, Murree, Punjab',
                'phone' => '+92-51-9269845',
                'whatsapp' => '+92-51-9269845',
                'email' => 'contact@greenvalleymurree.com',
                'check_in_time' => '15:00:00',
                'check_out_time' => '11:00:00',
                'total_rooms' => 18,
                'status' => 1,
            ],
            [
                'location_id' => Location::inRandomOrder()->first()->id ?? 1,
                'name' => 'Alpine Comfort Guest House Nathia Gali',
                'type' => 'guest house',
                'description' => 'Alpine Comfort Guest House in Nathia Gali provides a peaceful mountain escape surrounded by pine forests and breathtaking Himalayan views. Our guest house offers clean, comfortable rooms with essential amenities, delicious home-style Pakistani cuisine, and a welcoming family environment. Perfect for nature lovers, hikers, and those seeking respite from city life, the property is located near popular hiking trails and scenic viewpoints. Experience traditional hill station hospitality combined with modern comfort in one of Pakistan\'s most beautiful destinations.',
                'address' => 'Main Bazaar Road, Nathia Gali, Khyber Pakhtunkhwa',
                'phone' => '+92-992-410234',
                'whatsapp' => '+92-992-410234',
                'email' => 'info@alpinecomfort.com',
                'check_in_time' => '14:00:00',
                'check_out_time' => '11:00:00',
                'total_rooms' => 15,
                'status' => 1,
            ],
            [
                'location_id' => Location::inRandomOrder()->first()->id ?? 1,
                'name' => 'Hill View Guest House Abbottabad',
                'type' => 'guest house',
                'description' => 'Hill View Guest House Abbottabad is a charming accommodation option in the heart of this peaceful city. Offering budget-friendly rooms with basic amenities, the guest house caters to travelers seeking simple, clean, and comfortable lodging. With easy access to local markets, restaurants, and attractions, it serves as an excellent base for exploring the region. Our friendly staff ensures a pleasant stay with personalized service. The property is particularly popular among families visiting the area and tourists traveling to nearby hill stations like Nathia Gali and Ayubia.',
                'address' => 'Jinnah Road, Mandian, Abbottabad, Khyber Pakhtunkhwa',
                'phone' => '+92-992-383456',
                'whatsapp' => '+92-992-383456',
                'email' => 'contact@hillviewabbottabad.com',
                'check_in_time' => '15:00:00',
                'check_out_time' => '11:00:00',
                'total_rooms' => 12,
                'status' => 1,
            ],
            [
                'location_id' => Location::inRandomOrder()->first()->id ?? 1,
                'name' => 'Sunset Guest House Karachi',
                'type' => 'guest house',
                'description' => 'Sunset Guest House Karachi offers affordable and comfortable accommodation in a convenient location near the city center and main business districts. The guest house provides clean rooms with air conditioning, WiFi, and essential amenities at competitive rates. Ideal for budget-conscious business travelers, students, and tourists, the property focuses on providing value for money without compromising on cleanliness and comfort. Our helpful staff can assist with local transportation and sightseeing recommendations. Continental breakfast is included, and nearby restaurants offer diverse dining options.',
                'address' => 'M.A. Jinnah Road, Garden East, Karachi, Sindh',
                'phone' => '+92-21-32727890',
                'whatsapp' => '+92-21-32727890',
                'email' => 'info@sunsetguesthouse.com',
                'check_in_time' => '14:00:00',
                'check_out_time' => '11:00:00',
                'total_rooms' => 20,
                'status' => 1,
            ],

            // Resorts (3)
            [
                'location_id' => Location::inRandomOrder()->first()->id ?? 1,
                'name' => 'Shangrila Resort Skardu',
                'type' => 'resort',
                'description' => 'Shangrila Resort Skardu, also known as "Heaven on Earth," is a breathtaking resort situated by the crystal-clear Lower Kachura Lake in Baltistan. The resort offers luxury cottages and rooms with stunning lake and mountain views, authentic Balti cuisine at its lakeside restaurant, boating facilities, and guided tours to nearby attractions like Deosai Plains and K2 Base Camp. The resort combines natural beauty with modern comforts, making it an unforgettable destination for adventure seekers and nature lovers. Experience the magic of Gilgit-Baltistan with world-class hospitality.',
                'address' => 'Lower Kachura Lake, Skardu, Gilgit-Baltistan',
                'phone' => '+92-5815-960311',
                'whatsapp' => '+92-5815-960311',
                'email' => 'reservations@shangrilaskardu.com',
                'check_in_time' => '14:00:00',
                'check_out_time' => '11:00:00',
                'total_rooms' => 65,
                'status' => 1,
            ],
            [
                'location_id' => Location::inRandomOrder()->first()->id ?? 1,
                'name' => 'PC Bhurban Luxury Resort',
                'type' => 'resort',
                'description' => 'PC Bhurban Luxury Resort is an exclusive mountain retreat perched at 7,000 feet in the Murree hills, offering unparalleled luxury and natural beauty. This 5-star resort features elegantly designed rooms and suites with panoramic mountain views, fine dining restaurants, an 18-hole championship golf course, state-of-the-art spa and wellness center, indoor heated pool, and extensive recreational facilities. Perfect for corporate events, weddings, and family vacations, the resort provides a complete escape from urban life with activities ranging from golf and tennis to hiking and bird watching.',
                'address' => 'Murree Expressway, Bhurban, Punjab',
                'phone' => '+92-51-9269020',
                'whatsapp' => '+92-51-9269020',
                'email' => 'info@pcbhurban.com',
                'check_in_time' => '15:00:00',
                'check_out_time' => '12:00:00',
                'total_rooms' => 85,
                'status' => 1,
            ],
            [
                'location_id' => Location::inRandomOrder()->first()->id ?? 1,
                'name' => 'Pearl Continental Beach Resort Karachi',
                'type' => 'resort',
                'description' => 'Pearl Continental Beach Resort Karachi offers a unique beachfront experience on the Arabian Sea coastline. This resort combines the tranquility of seaside living with luxury amenities, featuring spacious rooms with ocean views, multiple dining options specializing in seafood and international cuisine, direct beach access, water sports facilities, swimming pools, kids\' club, and extensive event spaces. Ideal for weekend getaways, family vacations, and destination weddings, the resort provides a perfect escape from the bustling city while remaining easily accessible from Karachi\'s main areas.',
                'address' => 'Hawke\'s Bay Road, Sandspit Beach, Karachi, Sindh',
                'phone' => '+92-21-35065000',
                'whatsapp' => '+92-21-35065000',
                'email' => 'beachresort@pchotels.com',
                'check_in_time' => '14:00:00',
                'check_out_time' => '12:00:00',
                'total_rooms' => 72,
                'status' => 1,
            ],
        ];

        foreach ($hotels as $hotelData) {
            $hotel = Hotel::create($hotelData);
            echo "Created hotel: {$hotel->name} ({$hotel->type}) with {$hotel->total_rooms} rooms\n";
        }

        echo "\n✅ Successfully created " . count($hotels) . " hotels!\n";
    }
}
