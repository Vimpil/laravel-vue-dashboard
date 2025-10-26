<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    /**
     * Fetch all events from the database.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $events = Event::all(['id', 'title', 'outcomes']);
        $events->transform(function ($event) {
            $event->outcomes = json_decode($event->outcomes);
            return $event;
        });

        return response()->json($events);
    }
}
