<?php
namespace App\Services;

use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use App\Jobs\ExpireReservation;

class ReservationService
{
    protected int $ttlMinutes;

    public function __construct()
    {
        $this->ttlMinutes = (int) env('RESERVATION_TTL_MINUTES', 5);
    }

    /**
     * Reserve tickets for guests (no login)
     */
    public function reserve(Event $event, int $quantity = 1)
    {
        if (! $this->checkTicketSalesOpen($event)) {
            return ['error' => 'Ticket sales closed', 'code' => 403];
        }

        return $this->createReservation($event, $quantity);
    }

    /**
     * Purchase a reservation (guest, no user_id)
     */
    public function purchase(Reservation $reservation)
    {
        return DB::transaction(function () use ($reservation) {
            $res = Reservation::find($reservation->id);

            if (!$res || $res->status !== 'reserved') {
                return ['error' => 'Invalid reservation', 'code' => 409];
            }

            if ($res->expires_at && $res->expires_at->isPast()) {
                $res->status = 'expired';
                $res->save();
                Event::where('id', $res->event_id)->increment('available_tickets');
                return ['error' => 'Reservation expired', 'code' => 410];
            }

            $res->status = 'purchased';
            $res->reference_code = $this->generateReference();
            $res->save();

            return [
                'success' => true,
                'reference_code' => $res->reference_code
            ];
        });
    }

    /**
     * Create a reservation
     */
    private function createReservation(Event $event, int $quantity = 1)
    {
        return DB::transaction(function () use ($event, $quantity) {
            $affected = Event::whereKey($event->id)
                ->where('available_tickets', '>=', $quantity)
                ->decrement('available_tickets', $quantity);

            if ($affected === 0) {
                return ['error' => 'Sold out', 'code' => 409];
            }

            $reservation = Reservation::create([
                'event_id' => $event->id,
                'status' => 'reserved',
                'reserved_at' => now(),
                'expires_at' => now()->addMinutes($this->ttlMinutes),
            ]);

            ExpireReservation::dispatch($reservation)
                ->delay(now()->addMinutes($this->ttlMinutes));

            return ['reservation' => $reservation];
        });
    }

    /**
     * Check if ticket sales are open
     */
    private function checkTicketSalesOpen(Event $event): bool
    {
        $minutesBeforeEvent = env('TICKET_SALES_BEFORE_EVENT_MINUTES', 60);
        return now()->lessThanOrEqualTo($event->date->subMinutes($minutesBeforeEvent));
    }

    /**
     * Generate unique reference code
     */
    private function generateReference(int $length = 5): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        do {
            $reference = '';
            for ($i = 0; $i < $length; $i++) {
                $reference .= $characters[random_int(0, strlen($characters) - 1)];
            }
            $exists = Reservation::where('reference_code', $reference)->exists();
        } while ($exists);

        return $reference;
    }
}
