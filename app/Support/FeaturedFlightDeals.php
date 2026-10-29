<?php

namespace App\Support;

class FeaturedFlightDeals
{
    /**
     * [from code, from city, to code, to city, airline, USD price, image, airline IATA code]
     */
    public static function all(): array
    {
        return [
            ['LHE', 'Lahore', 'DXB', 'Dubai', 'Pakistan International', 100, 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=400&h=200&fit=crop', 'PK'],
            ['KUL', 'Kuala Lumpur', 'DXB', 'Dubai', 'Malaysia Airlines', 620, 'https://images.unsplash.com/photo-1596422846543-75c6fc197f07?w=400&h=200&fit=crop', 'MH'],
            ['BER', 'Berlin', 'IST', 'Istanbul', 'Turkish Airlines', 600, 'https://images.unsplash.com/photo-1527838832700-5059252407fa?w=400&h=200&fit=crop', 'TK'],
            ['DXB', 'Dubai', 'SHJ', 'Sharjah', 'Emirates', 460, 'https://images.unsplash.com/photo-1582672060674-bc2bd808a8b5?w=400&h=200&fit=crop', 'EK'],
            ['DAC', 'Dhaka', 'JED', 'Jeddah', 'Australian Airlines', 385, 'https://images.unsplash.com/photo-1591604021695-0c69b7c05981?w=400&h=200&fit=crop', 'AO'],
            ['DEL', 'Delhi', 'SVO', 'Moscow', 'Air India Limited', 760, 'https://images.unsplash.com/photo-1513326738677-b964603b136d?w=400&h=200&fit=crop', 'AI'],
            ['MNL', 'Manila', 'DXB', 'Dubai', 'Air Philippines', 450, 'https://images.unsplash.com/photo-1563789031959-4c02bcb41319?w=400&h=200&fit=crop', '2P'],
            ['SUB', 'Surabaya', 'JFK', 'New York', 'American Airlines', 800, 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?w=400&h=200&fit=crop', 'AA'],
            ['BER', 'Berlin', 'DXB', 'Dubai', 'Air Arabia', 240, 'https://images.unsplash.com/photo-1518684079-3c830dcef090?w=400&h=200&fit=crop', 'G9'],
        ];
    }
}