<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReservationResource;
use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Services\ReservationService;

class ReservationController extends Controller
{
    protected ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    /**
     * Reserve tickets for an event (guest, no login)
     */
    public function reserve(Request $req, Event $event)
    {
        $quantity = 1;
        $result = $this->reservationService->reserve($event, $quantity);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json([
            'message' => 'Reservation created',
            'reservation'=> new ReservationResource($result['reservation'])

        ], 201);
    }

    /**
     * Purchase a reserved ticket (guest, no login)
     */
    public function purchase(Request $req, Reservation $reservation)
    {
        $result = $this->reservationService->purchase($reservation);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json(['message' => 'Purchased', 'reference_code' => $result['reference_code']], 200);
    }
}
