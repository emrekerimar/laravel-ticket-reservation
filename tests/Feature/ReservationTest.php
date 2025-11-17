<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_reserve_ticket()
    {
        $event = Event::create([
            'name' => 'Test Event',
            'total_tickets' => 5,
            'available_tickets' => 5,
            'date' => now()->addDays(14),
        ]);

        $response = $this->postJson("/api/events/{$event->id}/reserve", [
            'quantity' => 1
        ]);

        $response->assertStatus(201);
        $this->assertEquals(4, $event->fresh()->available_tickets);
    }

    #[Test]
    public function prevents_overselling_under_concurrency()
    {
        $event = Event::create([
            'name' => 'Test Event 2',
            'total_tickets' => 5,
            'available_tickets' => 5,
            'date' => now()->addDays(14),
        ]);

        $results = [];

        DB::transaction(function () use ($event, &$results) {
            for ($i = 0; $i < 10; $i++) {
                $results[] = $this->postJson("/api/events/{$event->id}/reserve");
            }
        });


        $successful = 0;

        foreach ($results as $r) {
            if ($r->status() === 201) $successful++;
        }

        $this->assertEquals(5, $successful);

        $this->assertEquals(0, $event->fresh()->available_tickets);
    }

    #[Test]
    public function reservation_expires_correctly()
    {
        $event = Event::create([
            'name' => 'Test Event 3',
            'total_tickets' => 3,
            'available_tickets' => 3,
            'date' => now()->addDays(14),
        ]);

        $response = $this->postJson("/api/events/{$event->id}/reserve");
        $reservationId = $response->json('reservation.id');

        $reservation = Reservation::find($reservationId);
        $reservation->expires_at = now()->subMinutes(5);
        $reservation->save();

        \App\Jobs\ExpireReservation::dispatchSync($reservation);

        $this->assertEquals('expired', $reservation->fresh()->status);
        $this->assertEquals(3, $event->fresh()->available_tickets);
    }

    #[Test]
    public function user_can_purchase_reserved_ticket()
    {
        $event = Event::create([
            'name' => 'Test Event 4',
            'total_tickets' => 2,
            'available_tickets' => 2,
            'date' => now()->addDays(14),
        ]);

        $response = $this->postJson("/api/events/{$event->id}/reserve");
        $reservationId = $response->json('reservation.id');

        $purchase = $this->postJson("/api/reservations/{$reservationId}/purchase");

        $purchase->assertStatus(200);
        $purchase->assertJsonStructure(['reference_code']);

        $this->assertEquals('purchased', Reservation::find($reservationId)->status);
    }
}
