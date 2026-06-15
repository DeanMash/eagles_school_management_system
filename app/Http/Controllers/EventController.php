<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // For admin: show all events
        // For others: show only public events
        if (Auth::user()->user_type == 'admin') {
            $events = Event::orderBy('event_date', 'desc')->paginate(20);
        } else {
            $events = Event::where('is_public', true)
                        ->orderBy('event_date', 'desc')
                        ->paginate(20);
        }
        
        return view('events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('events.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'event_type' => 'required|in:academic,exam,sports,cultural,holiday,meeting,submission,workshop,event,other',
            'is_public' => 'boolean'
        ]);

        Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'venue' => $request->venue,
            'event_type' => $request->event_type,
            'created_by' => Auth::id(),
            'is_public' => $request->is_public ?? true
        ]);

        // Send notification to all users
        $this->notifyUsersAboutNewEvent($request->title, $request->event_date);

        return redirect()->route('events.index')
            ->with('success', 'Event created successfully!');
    }

    /**
     * Quick add event from dashboard modal.
     */
    public function quickAdd(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'event_type' => 'required|in:academic,exam,sports,cultural,holiday,meeting,submission,workshop,event',
            'description' => 'nullable|string',
            'venue' => 'nullable|string',
            'start_time' => 'nullable|date_format:H:i',
            'is_public' => 'nullable|boolean'
        ]);

        $validTypes = ['academic', 'exam', 'sports', 'cultural', 'holiday', 'meeting', 'submission', 'workshop', 'event', 'other'];
        $eventType = in_array($request->event_type, $validTypes) ? $request->event_type : 'event';

        $event = Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'venue' => $request->venue,
            'event_type' => $eventType,
            'created_by' => Auth::id(),
            'is_public' => $request->is_public ?? true
        ]);

        // Send notification to all users
        $this->notifyUsersAboutNewEvent($request->title, $request->event_date);

        return response()->json([
            'success' => true,
            'message' => 'Event added successfully',
            'event' => $event
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        // Check if user can view this event
        if (!$event->is_public && $event->created_by != Auth::id() && Auth::user()->user_type != 'admin') {
            abort(403, 'Unauthorized to view this event');
        }
        
        return response()->json($event);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        // Check if user can edit this event
        if ($event->created_by != Auth::id() && Auth::user()->user_type != 'admin') {
            abort(403, 'Unauthorized to edit this event');
        }
        
        return view('events.edit', compact('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        // Check if user can update this event
        if ($event->created_by != Auth::id() && Auth::user()->user_type != 'admin') {
            abort(403, 'Unauthorized to update this event');
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'event_type' => 'required|in:academic,exam,sports,cultural,holiday,meeting,submission,workshop,event,other',
            'is_public' => 'boolean'
        ]);

        $event->update($request->all());

        return redirect()->route('events.index')
            ->with('success', 'Event updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        // Check if user can delete this event
        if ($event->created_by != Auth::id() && Auth::user()->user_type != 'admin') {
            abort(403, 'Unauthorized to delete this event');
        }
        
        $event->delete();
        
        return redirect()->route('events.index')
            ->with('success', 'Event deleted successfully!');
    }

    /**
     * Get events by date range (for calendar) - ALL USERS CAN ACCESS
     */
    public function getByRange(Request $request)
    {
        $start = $request->start ?? now()->format('Y-m-d');
        $end = $request->end ?? now()->addMonth()->format('Y-m-d');

        // Admin/super_admin see all events, others see only public events
        $query = Event::whereBetween('event_date', [$start, $end]);
        
        if (!in_array(Auth::user()->user_type, ['admin', 'super_admin'])) {
            $query->where('is_public', true);
        }

        $events = $query->orderBy('event_date')->orderBy('start_time')->get()
            ->map(function($event) {
                $startDate = $event->event_date->format('Y-m-d');
                $start = $event->start_time
                    ? $startDate . 'T' . $event->start_time->format('H:i:s')
                    : $startDate;
                $end = null;
                if ($event->end_time) {
                    $end = $startDate . 'T' . $event->end_time->format('H:i:s');
                } elseif ($event->start_time) {
                    $end = $startDate . 'T23:59:59';
                }
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $start,
                    'end' => $end,
                    'description' => $event->description,
                    'venue' => $event->venue,
                    'event_type' => $event->event_type,
                    'start_time' => $event->start_time ? $event->start_time->format('h:i A') : null,
                    'end_time' => $event->end_time ? $event->end_time->format('h:i A') : null,
                    'color' => $this->getEventColor($event->event_type),
                    'textColor' => '#ffffff',
                    'is_public' => $event->is_public,
                    'created_by' => $event->created_by
                ];
            });

        return response()->json($events);
    }

    /**
     * Get events for specific date - ALL USERS CAN ACCESS
     */
    public function getByDate(Request $request)
    {
        $date = $request->date ?? now()->format('Y-m-d');

        // Admin sees all events, others see only public events
        $query = Event::whereDate('event_date', $date);
        
        if (Auth::user()->user_type != 'admin') {
            $query->where('is_public', true);
        }

        $events = $query->orderBy('start_time')->get();

        return response()->json($events);
    }

    /**
     * Get today's events for dashboard - ALL USERS CAN ACCESS
     */
    public function getTodayEvents()
    {
        $today = now()->format('Y-m-d');
        
        // Admin sees all events, others see only public events
        $query = Event::whereDate('event_date', $today);
        
        if (Auth::user()->user_type != 'admin') {
            $query->where('is_public', true);
        }

        $events = $query->orderBy('start_time')->get();
        
        return response()->json($events);
    }

    /**
     * Get upcoming events (next 7 days) - ALL USERS CAN ACCESS
     */
    public function getUpcomingEvents()
    {
        $today = now()->format('Y-m-d');
        $nextWeek = now()->addDays(7)->format('Y-m-d');
        
        // Admin sees all events, others see only public events
        $query = Event::whereBetween('event_date', [$today, $nextWeek]);
        
        if (Auth::user()->user_type != 'admin') {
            $query->where('is_public', true);
        }

        $events = $query->orderBy('event_date')->orderBy('start_time')->get();
        
        return response()->json($events);
    }

    /**
     * Toggle event public/private status (ADMIN ONLY)
     */
    public function toggleVisibility(Event $event)
    {
        if (Auth::user()->user_type != 'admin') {
            abort(403, 'Unauthorized');
        }

        $event->is_public = !$event->is_public;
        $event->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Event visibility updated',
            'is_public' => $event->is_public
        ]);
    }

    /**
     * Get event statistics for admin dashboard
     */
    public function getEventStats()
    {
        if (Auth::user()->user_type != 'admin') {
            abort(403, 'Unauthorized');
        }
        
        $stats = [
            'total_events' => Event::count(),
            'public_events' => Event::where('is_public', true)->count(),
            'private_events' => Event::where('is_public', false)->count(),
            'today_events' => Event::whereDate('event_date', today())->count(),
            'upcoming_events' => Event::whereDate('event_date', '>=', today())->count(),
            'events_by_type' => Event::select('event_type', DB::raw('count(*) as count'))
                ->groupBy('event_type')
                ->get()
                ->pluck('count', 'event_type')
        ];
        
        return response()->json($stats);
    }

    /**
     * Helper function for event colors
     */
    private function getEventColor($type)
    {
        $colors = [
            'academic' => '#007bff',
            'exam' => '#dc3545',
            'sports' => '#28a745',
            'cultural' => '#ffc107',
            'holiday' => '#17a2b8',
            'meeting' => '#6c757d',
            'submission' => '#ffc107',
            'workshop' => '#17a2b8',
            'event' => '#007bff',
            'other' => '#6f42c1'
        ];

        return $colors[$type] ?? '#6c757d';
    }

    /**
     * Helper function to notify users about new event
     */
    private function notifyUsersAboutNewEvent($title, $date)
    {
        // This is where you would add notification logic
        // For now, we'll just log it
        \Log::info("New event created: {$title} on {$date}");
        
        // You could add:
        // 1. Database notifications
        // 2. Email notifications
        // 3. SMS notifications
        // 4. Push notifications
        
        // Example database notification (uncomment if you have notifications table):
        /*
        $users = User::where('id', '!=', Auth::id())->get();
        foreach ($users as $user) {
            $user->notifications()->create([
                'type' => 'event_created',
                'data' => json_encode([
                    'title' => $title,
                    'date' => $date,
                    'message' => "New event: {$title} on " . \Carbon\Carbon::parse($date)->format('M d, Y')
                ]),
                'read_at' => null
            ]);
        }
        */
    }
}