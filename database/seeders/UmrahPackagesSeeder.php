<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Umrah;
use App\Models\UmrahImage;
use App\Models\UmrahInclusion;
use App\Models\UmrahExclusion;
use App\Models\UmrahPackageType;
use Carbon\Carbon;

class UmrahPackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, ensure package types exist
        $packageTypes = [
            ['packege_type' => 'Economy', 'status' => 1],
            ['packege_type' => 'Standard', 'status' => 1],
            ['packege_type' => 'Premium', 'status' => 1],
            ['packege_type' => 'VIP', 'status' => 1],
            ['packege_type' => 'Luxury', 'status' => 1],
        ];

        foreach ($packageTypes as $type) {
            UmrahPackageType::firstOrCreate(
                ['packege_type' => $type['packege_type']],
                $type
            );
        }

        // Create common inclusions
        $inclusions = [
            'Return flight tickets',
            'Hotel accommodation',
            'Airport transfers',
            'Visa processing',
            'Guided Ziyarat tours',
            'Free WiFi',
            'Breakfast included',
            '24/7 customer support',
            'Travel insurance',
            'Group leader assistance',
        ];

        foreach ($inclusions as $inclusion) {
            UmrahInclusion::firstOrCreate(['name' => $inclusion]);
        }

        // Create common exclusions
        $exclusions = [
            'Lunch and dinner',
            'Personal expenses',
            'Additional baggage',
            'Optional tours',
            'Tips and gratuities',
            'Laundry services',
            'Room service',
            'PCR test',
        ];

        foreach ($exclusions as $exclusion) {
            UmrahExclusion::firstOrCreate(['name' => $exclusion]);
        }

        // Create 10 real Umrah packages
        $packages = [
            [
                'name' => '14 Days Premium Umrah Package',
                'packege_type' => 'Premium',
                'currceny' => 'USD',
                'price' => 2499,
                'duration' => '14 Days / 13 Nights',
                'loaction' => 1,
                'leaving_from' => 4583, // New York
                'going_to' => 2802, // Jeddah
                'checkin_date' => Carbon::now()->addMonths(2)->format('Y-m-d'),
                'checkout_date' => Carbon::now()->addMonths(2)->addDays(14)->format('Y-m-d'),
                'night_in_mekkah' => 8,
                'night_in_madina' => 5,
                'class' => 'Business',
                'desc' => 'Experience the spiritual journey of a lifetime with our 14-day Premium Umrah Package. This comprehensive package includes luxurious 5-star accommodations near the Holy Mosques, comfortable business class flights, and guided tours of all significant Islamic historical sites. Our expert guides will ensure your pilgrimage is both spiritually fulfilling and comfortable.',
                'inclusions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                'exclusions' => [1, 2, 3, 4],
                'policy' => 'Free cancellation up to 30 days before departure. 50% refund between 15-30 days. No refund within 15 days of departure.',
                'featured' => '1',
                'status' => '1',
                'stars' => 5,
                'rating' => 4.8,
                'adults' => 2,
                'childs' => 0,
                'infants' => 0,
            ],
            [
                'name' => '10 Days Economy Umrah Package',
                'packege_type' => 'Economy',
                'currceny' => 'USD',
                'price' => 1299,
                'duration' => '10 Days / 9 Nights',
                'loaction' => 1,
                'leaving_from' => 8, // London
                'going_to' => 2802, // Jeddah
                'checkin_date' => Carbon::now()->addMonths(1)->format('Y-m-d'),
                'checkout_date' => Carbon::now()->addMonths(1)->addDays(10)->format('Y-m-d'),
                'night_in_mekkah' => 5,
                'night_in_madina' => 4,
                'class' => 'Economy',
                'desc' => 'Perfect for budget-conscious pilgrims, our 10-day Economy Umrah Package offers essential services for a comfortable spiritual journey. Stay in clean, well-located 3-star hotels within walking distance of the Harams. Includes economy flights, visa processing, and basic guided tours. Ideal for families and groups looking for affordable pilgrimage options.',
                'inclusions' => [1, 2, 3, 4, 5, 8, 10],
                'exclusions' => [1, 2, 3, 4, 5, 6],
                'policy' => 'Non-refundable booking. Date changes allowed up to 45 days before departure with $100 fee.',
                'featured' => '1',
                'status' => '1',
                'stars' => 3,
                'rating' => 4.2,
                'adults' => 2,
                'childs' => 1,
                'infants' => 0,
            ],
            [
                'name' => '21 Days VIP Umrah Package',
                'packege_type' => 'VIP',
                'currceny' => 'USD',
                'price' => 4999,
                'duration' => '21 Days / 20 Nights',
                'loaction' => 1,
                'leaving_from' => 9404, // Dubai
                'going_to' => 2802, // Jeddah
                'checkin_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'checkout_date' => Carbon::now()->addMonths(3)->addDays(21)->format('Y-m-d'),
                'night_in_mekkah' => 12,
                'night_in_madina' => 8,
                'class' => 'First Class',
                'desc' => 'Indulge in the ultimate Umrah experience with our exclusive 21-day VIP Package. Enjoy first-class flights, five-star hotels with Haram views, private transfers, and personalized services. This package includes extended stays in Makkah and Madinah, comprehensive Ziyarat tours, and dedicated personal assistance throughout your journey.',
                'inclusions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                'exclusions' => [2, 3, 4],
                'policy' => 'Flexible cancellation policy. Full refund up to 60 days before departure. 75% refund between 30-60 days.',
                'featured' => '1',
                'status' => '1',
                'stars' => 5,
                'rating' => 4.9,
                'adults' => 2,
                'childs' => 0,
                'infants' => 0,
            ],
            [
                'name' => '7 Days Express Umrah Package',
                'packege_type' => 'Standard',
                'currceny' => 'USD',
                'price' => 999,
                'duration' => '7 Days / 6 Nights',
                'loaction' => 1,
                'leaving_from' => 6, // Manchester
                'going_to' => 7, // Madinah
                'checkin_date' => Carbon::now()->addWeeks(3)->format('Y-m-d'),
                'checkout_date' => Carbon::now()->addWeeks(3)->addDays(7)->format('Y-m-d'),
                'night_in_mekkah' => 3,
                'night_in_madina' => 3,
                'class' => 'Economy',
                'desc' => 'Short on time but not on faith? Our 7-day Express Umrah Package is designed for busy professionals and those with limited vacation time. This efficient package covers all essential rituals with comfortable 4-star accommodations, direct flights, and streamlined services to maximize your spiritual experience in minimal time.',
                'inclusions' => [1, 2, 3, 4, 5, 7, 8, 10],
                'exclusions' => [1, 2, 3, 4, 5],
                'policy' => 'Standard cancellation policy. 50% refund up to 21 days before departure.',
                'featured' => '0',
                'status' => '1',
                'stars' => 4,
                'rating' => 4.5,
                'adults' => 1,
                'childs' => 0,
                'infants' => 0,
            ],
            [
                'name' => '15 Days Family Umrah Package',
                'packege_type' => 'Standard',
                'currceny' => 'USD',
                'price' => 3499,
                'duration' => '15 Days / 14 Nights',
                'loaction' => 1,
                'leaving_from' => 7393, // Toronto
                'going_to' => 2802, // Jeddah
                'checkin_date' => Carbon::now()->addMonths(2)->format('Y-m-d'),
                'checkout_date' => Carbon::now()->addMonths(2)->addDays(15)->format('Y-m-d'),
                'night_in_mekkah' => 8,
                'night_in_madina' => 6,
                'class' => 'Economy',
                'desc' => 'Specially designed for families, this 15-day package includes child-friendly accommodations, family rooms, and flexible scheduling. Stay in comfortable 4-star hotels with family suites, enjoy guided tours suitable for all ages, and benefit from our experienced family travel coordinators who ensure a smooth journey for parents and children alike.',
                'inclusions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                'exclusions' => [1, 2, 3],
                'policy' => 'Family-friendly cancellation policy. Free date changes up to 30 days before departure.',
                'featured' => '1',
                'status' => '1',
                'stars' => 4,
                'rating' => 4.6,
                'adults' => 2,
                'childs' => 2,
                'infants' => 1,
            ],
            [
                'name' => '12 Days Luxury Umrah Package',
                'packege_type' => 'Luxury',
                'currceny' => 'USD',
                'price' => 5999,
                'duration' => '12 Days / 11 Nights',
                'loaction' => 1,
                'leaving_from' => 4851, // Paris
                'going_to' => 2802, // Jeddah
                'checkin_date' => Carbon::now()->addMonths(4)->format('Y-m-d'),
                'checkout_date' => Carbon::now()->addMonths(4)->addDays(12)->format('Y-m-d'),
                'night_in_mekkah' => 7,
                'night_in_madina' => 4,
                'class' => 'First Class',
                'desc' => 'Experience unparalleled luxury with our premium 12-day package. Stay in the most prestigious hotels with direct Haram views, enjoy limousine transfers, personal butler service, and exclusive access to VIP lounges. This package is crafted for discerning travelers who seek the finest spiritual journey with exceptional comfort and privacy.',
                'inclusions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                'exclusions' => [2, 4],
                'policy' => 'Premium flexible policy. Full refund up to 45 days, 90% refund up to 30 days before departure.',
                'featured' => '1',
                'status' => '1',
                'stars' => 5,
                'rating' => 5.0,
                'adults' => 2,
                'childs' => 0,
                'infants' => 0,
            ],
            [
                'name' => '9 Days Ramadan Special Umrah',
                'packege_type' => 'Premium',
                'currceny' => 'USD',
                'price' => 3299,
                'duration' => '9 Days / 8 Nights',
                'loaction' => 1,
                'leaving_from' => 9, // Istanbul
                'going_to' => 7, // Madinah
                'checkin_date' => Carbon::now()->addMonths(5)->format('Y-m-d'),
                'checkout_date' => Carbon::now()->addMonths(5)->addDays(9)->format('Y-m-d'),
                'night_in_mekkah' => 5,
                'night_in_madina' => 3,
                'class' => 'Business',
                'desc' => 'Perform Umrah during the blessed month of Ramadan with our special 9-day package. Includes Suhoor and Iftar meals, special prayers at the Harams, and convenient hotel locations for easy access during Taraweeh. Experience the spiritual intensity of Ramadan in the holy cities with our expertly planned schedule and premium services.',
                'inclusions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                'exclusions' => [2, 3, 4],
                'policy' => 'Special Ramadan policy. Limited availability. 60% refund up to 45 days before departure.',
                'featured' => '1',
                'status' => '1',
                'stars' => 5,
                'rating' => 4.9,
                'adults' => 2,
                'childs' => 0,
                'infants' => 0,
            ],
            [
                'name' => '8 Days Budget Umrah Package',
                'packege_type' => 'Economy',
                'currceny' => 'USD',
                'price' => 899,
                'duration' => '8 Days / 7 Nights',
                'loaction' => 1,
                'leaving_from' => 18, // Cairo
                'going_to' => 2802, // Jeddah
                'checkin_date' => Carbon::now()->addWeeks(6)->format('Y-m-d'),
                'checkout_date' => Carbon::now()->addWeeks(6)->addDays(8)->format('Y-m-d'),
                'night_in_mekkah' => 4,
                'night_in_madina' => 3,
                'class' => 'Economy',
                'desc' => 'Our most affordable Umrah package without compromising on essential services. Perfect for first-time pilgrims and budget travelers. Includes basic but comfortable 3-star accommodations, economy flights, visa assistance, and fundamental guided tours. All essential services covered to ensure a successful pilgrimage.',
                'inclusions' => [1, 2, 3, 4, 8, 10],
                'exclusions' => [1, 2, 3, 4, 5, 6, 7],
                'policy' => 'Budget policy. Non-refundable. Date changes up to 60 days before with additional fee.',
                'featured' => '0',
                'status' => '1',
                'stars' => 3,
                'rating' => 4.0,
                'adults' => 1,
                'childs' => 0,
                'infants' => 0,
            ],
            [
                'name' => '18 Days Extended Umrah Package',
                'packege_type' => 'Standard',
                'currceny' => 'USD',
                'price' => 2899,
                'duration' => '18 Days / 17 Nights',
                'loaction' => 1,
                'leaving_from' => 3409, // Kuala Lumpur
                'going_to' => 7, // Madinah
                'checkin_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'checkout_date' => Carbon::now()->addMonths(3)->addDays(18)->format('Y-m-d'),
                'night_in_mekkah' => 10,
                'night_in_madina' => 7,
                'class' => 'Economy',
                'desc' => 'Take your time to immerse yourself in the spiritual atmosphere with our extended 18-day package. Enjoy longer stays in both holy cities, multiple Ziyarat tours, and ample time for worship and reflection. Ideal for those seeking a deeper spiritual connection and comprehensive exploration of Islamic historical sites.',
                'inclusions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                'exclusions' => [1, 2, 3, 4],
                'policy' => 'Standard cancellation. 70% refund up to 30 days, 40% refund up to 15 days before departure.',
                'featured' => '0',
                'status' => '1',
                'stars' => 4,
                'rating' => 4.7,
                'adults' => 2,
                'childs' => 0,
                'infants' => 0,
            ],
            [
                'name' => '11 Days Senior Citizen Umrah Package',
                'packege_type' => 'Premium',
                'currceny' => 'USD',
                'price' => 2799,
                'duration' => '11 Days / 10 Nights',
                'loaction' => 1,
                'leaving_from' => 677, // Birmingham
                'going_to' => 2802, // Jeddah
                'checkin_date' => Carbon::now()->addMonths(2)->format('Y-m-d'),
                'checkout_date' => Carbon::now()->addMonths(2)->addDays(11)->format('Y-m-d'),
                'night_in_mekkah' => 6,
                'night_in_madina' => 4,
                'class' => 'Business',
                'desc' => 'Specially designed for elderly pilgrims with mobility considerations and health support. Includes wheelchair-accessible hotels closest to the Harams, personal assistance, medical support on call, comfortable pacing, and transportation with easy access. Our trained staff ensures safety, comfort, and spiritual fulfillment for senior travelers.',
                'inclusions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                'exclusions' => [1, 3, 4],
                'policy' => 'Senior-friendly policy. Flexible dates. 80% refund up to 21 days before departure.',
                'featured' => '1',
                'status' => '1',
                'stars' => 5,
                'rating' => 4.8,
                'adults' => 2,
                'childs' => 0,
                'infants' => 0,
            ],
        ];

        // Image path template
        $imagePath = 'uploads/umrah/';

        foreach ($packages as $index => $packageData) {
            // Create the package
            $package = Umrah::create($packageData);

            // Add 8 images for each package
            for ($i = 1; $i <= 8; $i++) {
                UmrahImage::create([
                    'umrah_id' => $package->id,
                    'image' => $imagePath . 'package_' . $package->id . '_image_' . $i . '.jpg',
                ]);
            }

            echo "Created package: {$package->name} with 8 images\n";
        }

        echo "\n✅ Successfully created 10 Umrah packages with 80 images total!\n";
    }
}
