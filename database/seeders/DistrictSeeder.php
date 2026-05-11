<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $districts = [
            'Dhaka',
            'Gazipur',
            'Narayanganj',
            'Narsingdi',
            'Munshiganj',
            'Manikganj',
            'Tangail',
            'Faridpur',
            'Gopalganj',
            'Madaripur',
            'Shariatpur',
            'Rajbari',
            'Chattogram',
            'Cox\'s Bazar',
            'Cumilla',
            'Brahmanbaria',
            'Chandpur',
            'Feni',
            'Lakshmipur',
            'Noakhali',
            'Khagrachari',
            'Rangamati',
            'Bandarban',
            'Rajshahi',
            'Natore',
            'Naogaon',
            'Chapainawabganj',
            'Pabna',
            'Sirajganj',
            'Bogura',
            'Joypurhat',
            'Khulna',
            'Bagerhat',
            'Satkhira',
            'Jashore',
            'Jhenaidah',
            'Magura',
            'Narail',
            'Kushtia',
            'Meherpur',
            'Chuadanga',
            'Barishal',
            'Patuakhali',
            'Bhola',
            'Pirojpur',
            'Jhalokathi',
            'Barguna',
            'Sylhet',
            'Moulvibazar',
            'Habiganj',
            'Sunamganj',
            'Rangpur',
            'Dinajpur',
            'Thakurgaon',
            'Panchagarh',
            'Nilphamari',
            'Lalmonirhat',
            'Kurigram',
            'Gaibandha',
            'Mymensingh',
            'Jamalpur',
            'Sherpur',
            'Netrokona',
            'Kishoreganj',
        ];

        foreach ($districts as $name) {
            District::firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}
