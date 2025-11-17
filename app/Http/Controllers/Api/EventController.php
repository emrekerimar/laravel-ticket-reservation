<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::where('date', '>=', now())
            ->orderBy('date', 'asc')
            ->get();

        return EventResource::collection($events);
    }

    public function show($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        if ($event->date < now()) {
            return response()->json(['message'=>'Event not found'],404);
        }

        return new EventResource($event);
    }

}
