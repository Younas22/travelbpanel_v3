<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\HotelPolicy;

class HotelPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotels = Hotel::all();
        $totalPolicies = 0;

        foreach ($hotels as $hotel) {
            // Base policies (same for all)
            $policies = [
                [
                    'hotel_id' => $hotel->id,
                    'policy_type' => 'cancellation',
                    'description' => 'Free cancellation up to 24 hours before check-in. After that, one night charge applies. No-shows will be charged the full booking amount.',
                ],
                [
                    'hotel_id' => $hotel->id,
                    'policy_type' => 'smoking',
                    'description' => 'This is a non-smoking property. Smoking is only allowed in designated outdoor areas. A cleaning fee of PKR 5000 will be charged for violations.',
                ],
                [
                    'hotel_id' => $hotel->id,
                    'policy_type' => 'family',
                    'description' => 'Children under 12 stay free when using existing bedding. Extra bed charges apply for children above 12. Maximum 2 children per room.',
                ],
                [
                    'hotel_id' => $hotel->id,
                    'policy_type' => 'payment',
                    'description' => 'We accept cash, credit cards (Visa, MasterCard), and bank transfers. Full payment required at check-in. Online advance payment available for reservations.',
                ],
            ];

            // Pet policy varies
            $petAllowed = rand(0, 1) === 1; // 50% allow pets
            $policies[] = [
                'hotel_id' => $hotel->id,
                'policy_type' => 'pet',
                'description' => $petAllowed
                    ? 'Pets are allowed with prior approval. Additional cleaning fee of PKR 2000 applies. Maximum one pet per room. Pets must be kept on leash in common areas.'
                    : 'Pets are not allowed on the property for hygiene and safety reasons. Service animals are permitted with proper documentation.',
            ];

            foreach ($policies as $policy) {
                HotelPolicy::create($policy);
                $totalPolicies++;
            }

            echo "Created 5 policies for {$hotel->name}\n";
        }

        echo "\n✅ Successfully created {$totalPolicies} hotel policies!\n";
    }
}
