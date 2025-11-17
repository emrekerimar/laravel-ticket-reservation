<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ExpireReservation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $reservationId;

    /**
     * Create a new job instance.
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservationId = $reservation->id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            $res = Reservation::find($this->reservationId);

            if (!$res) return;

            if ($res->status !== 'reserved') return;

            if ($res->expires_at && $res->expires_at->isFuture()) return;

            $res->status = 'expired';
            $res->save();

            Event::where('id', $res->event_id)->increment('available_tickets');
        });
    }
}
