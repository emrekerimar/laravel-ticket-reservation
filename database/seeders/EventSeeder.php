<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::create([
            'id'=>'019a92dc-079f-70d6-9bb8-aa228b25511a',
            'name' => 'Rock Concert',
            'total_tickets' => 100,
            'available_tickets' => 100,
            'date' => now()->addDays(7),
        ]);

        Event::create([
            'name' => 'Jazz Night',
            'total_tickets' => 50,
            'available_tickets' => 50,
            'date' => now()->addDays(14),
        ]);
    }
}
