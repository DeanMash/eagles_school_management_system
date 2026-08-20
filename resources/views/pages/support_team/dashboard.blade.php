@extends('layouts.master')
@section('page_title', 'My Dashboard')
@section('content')

@php
    // Ensure variables exist with safe defaults
    $users = $users ?? collect();
    $todayEvents = $todayEvents ?? collect();
@endphp

{{-- STUDENT SPECIFIC DASHBOARD SECTION --}}
@if(Auth::check() && Auth::user()->user_type === 'student')
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <img src="{{ Auth::user()->photo }}" alt="Profile Picture" class="rounded-circle" style="width: 80px; height: 80px; border: 3px solid #007bff; object-fit: cover;">
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="mb-1">{{ Auth::user()->name }}</h4>
                                <p class="text-muted mb-0">
                                    <i class="icon-user"></i> Student Account
                                    @if(Qs::findStudentRecord(Auth::user()->id))
                                        | <i class="icon-book"></i> {{ Qs::findStudentRecord(Auth::user()->id)->my_class->name ?? 'N/A' }}
                                        | <i class="icon-id-card"></i> {{ Qs::findStudentRecord(Auth::user()->id)->adm_no ?? 'N/A' }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-tachometer-alt"></i> Student Dashboard
                            <small class="float-right">Welcome, {{ Auth::user()->name }}!</small>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column: Today's Classes (To-Do List) -->
                            <div class="col-lg-6">
                                <!-- Today's Schedule Card -->
                                <div class="card mb-4">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-tasks"></i> Today's Schedule
                                            <span class="badge badge-light float-right">{{ \Carbon\Carbon::now()->format('l, F j, Y') }}</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="todays-classes">
                                            @php
                                                $today = \Carbon\Carbon::today();
                                                $currentTime = \Carbon\Carbon::now();
                                                $hasTimetable = isset($today_timetable) && count($today_timetable) > 0;
                                            @endphp
                                            
                                            @if($hasTimetable)
                                                <div class="alert alert-success">
                                                    <i class="icon-checkmark-circle"></i> 
                                                    You have {{ count($today_timetable) }} class{{ count($today_timetable) > 1 ? 'es' : '' }} scheduled for today.
                                                </div>
                                                
                                                <div class="list-group">
                                                    @foreach($today_timetable as $class)
                                                        @php
                                                            // Determine if class is upcoming, current, or completed
                                                            $timeParts = explode(' - ', $class['time']);
                                                            $startTime = isset($timeParts[0]) ? \Carbon\Carbon::parse($timeParts[0]) : null;
                                                            $endTime = isset($timeParts[1]) ? \Carbon\Carbon::parse($timeParts[1]) : null;
                                                            
                                                            $status = 'upcoming';
                                                            $badgeClass = 'badge-warning';
                                                            if ($startTime && $endTime) {
                                                                if ($currentTime->between($startTime, $endTime)) {
                                                                    $status = 'current';
                                                                    $badgeClass = 'badge-success';
                                                                } elseif ($currentTime->gt($endTime)) {
                                                                    $status = 'completed';
                                                                    $badgeClass = 'badge-secondary';
                                                                }
                                                            }
                                                        @endphp
                                                        <div class="list-group-item list-group-item-action {{ $status === 'current' ? 'active' : '' }}">
                                                        <div class="d-flex w-100 justify-content-between">
                                                            <h6 class="mb-1">
                                                                    <i class="icon-book text-primary"></i> {{ $class['subject'] }}
                                                            </h6>
                                                                <span class="badge {{ $badgeClass }}">
                                                                    {{ ucfirst($status) }}
                                                                </span>
                                                        </div>
                                                        <p class="mb-1">
                                                                <small><i class="icon-clock"></i> {{ $class['time'] }}</small>
                                                        </p>
                                                    </div>
                                                    @endforeach
                                                        </div>
                                            @else
                                                <div class="alert alert-info">
                                                    <i class="icon-info"></i> 
                                                    @if(isset($student_record))
                                                        Your timetable for class <strong>{{ $class_name ?? 'N/A' }}</strong> hasn't been set up yet.
                                                    @else
                                                        Your timetable information is not available. Please contact administration.
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Weekly Overview -->
                                <div class="card">
                                    <div class="card-header bg-secondary text-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">
                                                <i class="icon-calendar"></i> Weekly Timetable
                                            </h5>
                                            <a href="{{ route('student.timetable_grid') }}" class="btn btn-sm btn-light mr-1">
                                                <i class="icon-grid6 mr-1"></i>Grid View
                                            </a>
                                            <a href="{{ route('student.weekly_timetable') }}" class="btn btn-sm btn-primary">
                                                <i class="icon-calendar52 mr-1"></i>Weekly View
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @if(isset($weekly_timetable) && $weekly_timetable->count() > 0)
                                        <div class="list-group">
                                            @php
                                                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                                $todayIndex = \Carbon\Carbon::now()->dayOfWeekIso - 1;
                                            @endphp
                                            
                                                @foreach($days as $index => $dayName)
                                                    @php
                                                        $dayClasses = $weekly_timetable->get($dayName, collect());
                                                        $isToday = $index == $todayIndex;
                                                    @endphp
                                                    <div class="list-group-item list-group-item-action {{ $isToday ? 'active' : '' }}">
                                                    <div class="d-flex w-100 justify-content-between">
                                                            <h6 class="mb-1">
                                                                {{ $dayName }}
                                                                @if($isToday)
                                                                    <span class="badge badge-info">Today</span>
                                                                @endif
                                                            </h6>
                                                            <small>{{ $dayClasses->count() }} class{{ $dayClasses->count() != 1 ? 'es' : '' }}</small>
                                                    </div>
                                                        @if($dayClasses->count() > 0)
                                                            @foreach($dayClasses->take(3) as $class)
                                                    <p class="mb-1">
                                                                    <small>
                                                                        @if(isset($class->time_slot))
                                                                            {{ $class->time_slot->full ?? ($class->time_slot->timestamp_from . ' - ' . $class->time_slot->timestamp_to) }}
                                                                        @endif
                                                                        - {{ $class->subject->name ?? 'N/A' }}
                                                                    </small>
                                                                </p>
                                                            @endforeach
                                                            @if($dayClasses->count() > 3)
                                                                <small class="text-muted">+ {{ $dayClasses->count() - 3 }} more</small>
                                                            @endif
                                                        @else
                                                            <p class="mb-1"><small class="text-muted">No classes scheduled</small></p>
                                                        @endif
                                                </div>
                                                @endforeach
                                        </div>
                                        @else
                                            <div class="alert alert-info">
                                                <i class="icon-info"></i> Weekly timetable not available.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column: Calendar & Events -->
                            <div class="col-lg-6">
                                <!-- Semester Calendar -->
                                <div class="card mb-4">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-calendar-alt"></i> Semester Calendar
                                            <small class="float-right">Events from Administration</small>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="calendar" style="min-height: 300px;"></div>
                                    </div>
                                </div>
                                
                                <!-- Upcoming Events - REPLACED SECTION -->
                                <div class="card">
                                    <div class="card-header bg-warning text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-bell"></i> Upcoming Events
                                            <small class="float-right">Added by Admin</small>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="upcoming-events">
                                            @php
                                                $events = $upcoming_events ?? collect();
                                            @endphp
                                            
                                            @if($events && $events->count() > 0)
                                                <div class="list-group">
                                                    @foreach($events as $event)
                                                        @php
                                                            $daysDiff = $event->event_date->diffInDays(\Carbon\Carbon::today());
                                                        @endphp
                                                        <div class="list-group-item event-item" data-event-id="{{ $event->id }}">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <h6 class="mb-1">{{ $event->title }}</h6>
                                                                    <small class="text-muted">
                                                                        <i class="far fa-calendar"></i> 
                                                                        {{ $event->event_date->format('M j, Y (D)') }}
                                                                        @if($event->start_time)
                                                                            <i class="far fa-clock ms-2"></i> 
                                                                            {{ date('h:i A', strtotime($event->start_time)) }}
                                                                        @endif
                                                                        @if($daysDiff == 0)
                                                                            <span class="badge badge-info ml-2">Today</span>
                                                                        @elseif($daysDiff == 1)
                                                                            <span class="badge badge-warning ml-2">Tomorrow</span>
                                                                        @elseif($daysDiff <= 7)
                                                                            <span class="text-warning ml-2">(in {{ $daysDiff }} days)</span>
                                                                        @endif
                                                                    </small>
                                                                </div>
                                                                <span class="badge 
                                                                    @if($event->event_type == 'holiday') badge-success
                                                                    @elseif($event->event_type == 'exam') badge-danger
                                                                    @elseif($event->event_type == 'submission') badge-warning
                                                                    @elseif($event->event_type == 'workshop') badge-info
                                                                    @elseif($event->event_type == 'academic') badge-primary
                                                                    @elseif($event->event_type == 'sports') badge-success
                                                                    @elseif($event->event_type == 'cultural') badge-warning
                                                                    @elseif($event->event_type == 'meeting') badge-secondary
                                                                    @else badge-primary @endif">
                                                                    {{ ucfirst($event->event_type) }}
                                                                </span>
                                                            </div>
                                                            @if($event->description)
                                                                <p class="mt-2 mb-0"><small>{{ \Illuminate\Support\Str::limit($event->description, 100) }}</small></p>
                                                            @endif
                                                            @if($event->venue)
                                                                <small class="text-muted">
                                                                    <i class="fas fa-map-marker-alt"></i> {{ $event->venue }}
                                                                </small>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i> 
                                                    No upcoming events scheduled.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Quick Stats -->
                                <div class="card mt-4">
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <h3 class="text-primary" id="today-classes-count">3</h3>
                                                <small class="text-muted">Today's Classes</small>
                                            </div>
                                            <div class="col-4">
                                                <h3 class="text-success" id="upcoming-events-count">
                                                    {{ $events ? $events->count() : 0 }}
                                                </h3>
                                                <small class="text-muted">Upcoming Events</small>
                                            </div>
                                            <div class="col-4">
                                                <h3 class="text-info">14</h3>
                                                <small class="text-muted">Weekly Classes</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Dashboard Footer -->
                        <div class="mt-4 text-center text-muted">
                            <small>
                                <i class="fas fa-info-circle"></i> 
                                This dashboard shows your daily schedule and semester activities. 
                                All events are managed by the administration.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        #calendar {
            height: 300px;
        }
        
        .fc-toolbar-title {
            font-size: 1.1rem !important;
        }
        
        .fc-button {
            padding: 0.3rem 0.5rem !important;
            font-size: 0.9rem !important;
        }
        
        .list-group-item.active {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }
        
        .event-item {
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .event-item:hover {
            background-color: #f8f9fa;
            transform: translateX(2px);
        }
    </style>
    
    <script>
    $(document).ready(function() {
        
        // Add click handlers to event items
        $('.event-item').click(function() {
            const eventId = $(this).data('event-id');
            
            // Fetch event details
            $.ajax({
                url: '{{ url("/events") }}/' + eventId,
                method: 'GET',
                success: function(event) {
                    const eventDate = new Date(event.event_date).toLocaleDateString('en-US', { 
                        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
                    });
                    
                    const modalHtml = `
                    <div class="modal fade" id="eventDetailModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">${event.title}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Date:</strong> ${eventDate}</p>
                                    <p><strong>Type:</strong> 
                                        <span class="badge ${getBadgeClass(event.event_type)}">
                                            ${event.event_type.charAt(0).toUpperCase() + event.event_type.slice(1)}
                                        </span>
                                    </p>
                                    ${event.description ? `<p><strong>Description:</strong><br>${event.description}</p>` : ''}
                                    ${event.venue ? `<p><strong>Venue:</strong> ${event.venue}</p>` : ''}
                                    ${event.start_time ? `<p><strong>Time:</strong> ${event.start_time} ${event.end_time ? ' - ' + event.end_time : ''}</p>` : ''}
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;
                    
                    // Remove existing modal if any
                    $('#eventDetailModal').remove();
                    $('body').append(modalHtml);
                    const modal = new bootstrap.Modal(document.getElementById('eventDetailModal'));
                    modal.show();
                }
            });
        });
        
        // Auto-refresh page every 30 minutes
        setTimeout(function() {
            location.reload();
        }, 30 * 60 * 1000);
        
        // Update class status based on current time
        function updateClassStatus() {
            const now = new Date();
            const currentHour = now.getHours();
            const currentMinute = now.getMinutes();
            
            $('.list-group-item').each(function() {
                const timeText = $(this).find('.fa-clock').parent().text();
                const match = timeText.match(/(\d+):(\d+)\s*(AM|PM)/);
                
                if (match) {
                    let hour = parseInt(match[1]);
                    const minute = parseInt(match[2]);
                    const ampm = match[3];
                    
                    // Convert to 24-hour format
                    if (ampm === 'PM' && hour < 12) hour += 12;
                    if (ampm === 'AM' && hour === 12) hour = 0;
                    
                    // Simple status update
                    const classTime = hour * 60 + minute;
                    const currentTime = currentHour * 60 + currentMinute;
                    
                    if (currentTime >= classTime && currentTime < classTime + 90) { // 1.5 hour class
                        $(this).find('.badge').removeClass('badge-warning badge-secondary').addClass('badge-success').text('In Progress');
                    } else if (currentTime > classTime + 90) {
                        $(this).find('.badge').removeClass('badge-warning badge-success').addClass('badge-secondary').text('Completed');
                    }
                }
            });
        }
        
        // Update status every minute
        setInterval(updateClassStatus, 60000);
        updateClassStatus(); // Initial update
        
        // Helper functions for badge and color
        function getBadgeClass(eventType) {
            const classes = {
                'academic': 'bg-primary',
                'exam': 'bg-danger',
                'sports': 'bg-success',
                'cultural': 'bg-warning',
                'holiday': 'bg-info',
                'meeting': 'bg-secondary',
                'submission': 'bg-warning',
                'workshop': 'bg-info',
                'event': 'bg-primary'
            };
            return classes[eventType] || 'bg-dark';
        }
        
        function getEventColor(eventType) {
            const colors = {
                'academic': '#007bff',
                'exam': '#dc3545',
                'sports': '#28a745',
                'cultural': '#ffc107',
                'holiday': '#17a2b8',
                'meeting': '#6c757d',
                'submission': '#ffc107',
                'workshop': '#17a2b8',
                'event': '#007bff'
            };
            return colors[eventType] || '#6c757d';
        }
    });
    </script>
@else
    {{-- EXISTING DASHBOARD FOR ADMIN/TEACHERS --}}
    @if(Qs::userIsTeamSA())
       <div class="row">
           <div class="col-sm-6 col-xl-3">
               <div class="card card-body bg-blue-400 has-bg-image">
                   <div class="media">
                       <div class="media-body">
                           <h3 class="mb-0">{{ isset($users) ? $users->where('user_type', 'student')->count() : 0 }}</h3>
                           <span class="text-uppercase font-size-xs font-weight-bold">Total Students</span>
                       </div>

                       <div class="ml-3 align-self-center">
                           <i class="icon-users4 icon-3x opacity-75"></i>
                       </div>
                   </div>
               </div>
           </div>

           <div class="col-sm-6 col-xl-3">
               <div class="card card-body bg-danger-400 has-bg-image">
                   <div class="media">
                       <div class="media-body">
                           <h3 class="mb-0">{{ isset($users) ? $users->where('user_type', 'teacher')->count() : 0 }}</h3>
                           <span class="text-uppercase font-size-xs">Total Teachers</span>
                       </div>

                       <div class="ml-3 align-self-center">
                           <i class="icon-users2 icon-3x opacity-75"></i>
                       </div>
                   </div>
               </div>
           </div>

           <div class="col-sm-6 col-xl-3">
               <div class="card card-body bg-success-400 has-bg-image">
                   <div class="media">
                       <div class="mr-3 align-self-center">
                           <i class="icon-pointer icon-3x opacity-75"></i>
                       </div>

                       <div class="media-body text-right">
                           <h3 class="mb-0">{{ isset($users) ? $users->where('user_type', 'admin')->count() : 0 }}</h3>
                           <span class="text-uppercase font-size-xs">Total Administrators</span>
                       </div>
                   </div>
               </div>
           </div>

           <div class="col-sm-6 col-xl-3">
               <div class="card card-body bg-indigo-400 has-bg-image">
                   <div class="media">
                       <div class="mr-3 align-self-center">
                           <i class="icon-user icon-3x opacity-75"></i>
                       </div>

                       <div class="media-body text-right">
                           <h3 class="mb-0">{{ isset($users) ? $users->where('user_type', 'parent')->count() : 0 }}</h3>
                           <span class="text-uppercase font-size-xs">Total Parents</span>
                       </div>
                   </div>
               </div>
           </div>
       </div>
    @endif

    {{--Events Calendar Begins--}}
    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title">School Events Calendar</h5>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card calendar-card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-calendar-alt"></i> School Calendar
                </h5>
                <div class="card-options">
                    <span class="badge bg-primary">Events: {{ isset($todayEvents) ? $todayEvents->count() : 0 }}</span>
                </div>
            </div>
            <div class="card-body">
                <!-- Date selector -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <input type="date" id="eventDatePicker" class="form-control form-control-sm" 
                               value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <button id="showTodayBtn" class="btn btn-sm btn-primary w-100">Today</button>
                    </div>
                </div>
                
                <!-- Events display for selected date -->
                <div id="dateEventsDisplay" class="mb-3">
                    <div class="text-center py-3">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading events...</span>
                        </div>
                    </div>
                </div>
                
                <!-- Calendar widget -->
                <div id="miniCalendar" class="fullcalendar-basic"></div>
                
                <!-- Quick add event (for admin) -->
                @if(auth()->check() && auth()->user()->user_type == 'admin')
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-primary w-100" 
                            data-bs-toggle="modal" 
                            data-bs-target="#addEventModal">
                        <i class="fas fa-plus"></i> Add Event
                    </button>
                </div>
                @endif
            </div>
        </div>

        <!-- Add Event Modal (for admin) - UPDATED VERSION -->
        @if(auth()->check() && auth()->user()->user_type == 'admin')
        <div class="modal fade" id="addEventModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">Add Event</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="quickEventForm" action="{{ route('events.quick-add') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Title *</label>
                                    <input type="text" name="title" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Event Type *</label>
                                    <select name="event_type" class="form-control form-control-sm" required>
                                        <option value="event">General Event</option>
                                        <option value="academic">Academic</option>
                                        <option value="exam">Exam</option>
                                        <option value="sports">Sports</option>
                                        <option value="cultural">Cultural</option>
                                        <option value="holiday">Holiday</option>
                                        <option value="meeting">Meeting</option>
                                        <option value="submission">Submission</option>
                                        <option value="workshop">Workshop</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Event Date *</label>
                                    <input type="date" name="event_date" class="form-control form-control-sm" required 
                                           value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Time (Optional)</label>
                                    <input type="time" name="start_time" class="form-control form-control-sm">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label>Description (Optional)</label>
                                <textarea name="description" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Venue (Optional)</label>
                                    <input type="text" name="venue" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Visibility</label>
                                    <select name="is_public" class="form-control form-control-sm">
                                        <option value="1">Public (All Students)</option>
                                        <option value="0">Private</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-sm btn-primary">Add Event</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
    {{--Events Calendar Ends--}}
@endif

@endsection

@push('css')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
.calendar-card {
    height: 450px;
    overflow: hidden;
}
#miniCalendar {
    height: 300px;
}
.fc .fc-toolbar {
    padding: 5px;
    margin-bottom: 5px;
}
.fc .fc-toolbar-title {
    font-size: 1em;
}
.fc .fc-button {
    padding: 2px 6px;
    font-size: 0.8em;
}
.fc-daygrid-event {
    font-size: 0.8em;
    padding: 1px 3px;
    margin: 1px 0;
}
.fc-event {
    cursor: pointer;
}
.event-highlight {
    background-color: #e7f1ff;
    border-left: 3px solid #007bff;
}
</style>
@endpush

@push('js')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script>
$(document).ready(function() {
    @if(Auth::check() && Auth::user()->user_type === 'student')
        // STUDENT CALENDAR - runs after FullCalendar 5 is loaded
        var studentCalendarEl = document.getElementById('calendar');
        if (studentCalendarEl && typeof FullCalendar !== 'undefined' && FullCalendar.Calendar) {
            var studentCalendar = new FullCalendar.Calendar(studentCalendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: { left: 'prev,next', center: 'title', right: 'today' },
                events: function(fetchInfo, successCallback) {
                    $.ajax({
                        url: '{{ url(route("events.get-by-range")) }}',
                        method: 'GET',
                        data: { start: fetchInfo.startStr, end: fetchInfo.endStr },
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        success: function(events) {
                            var formatted = (events || []).map(function(e) {
                                return {
                                    id: e.id, title: e.title, start: e.start, end: e.end || null,
                                    color: e.color, textColor: e.textColor || '#ffffff',
                                    extendedProps: {
                                        type: e.event_type, description: e.description,
                                        venue: e.venue,
                                        time: e.start_time ? (e.start_time + (e.end_time ? ' - ' + e.end_time : '')) : ''
                                    }
                                };
                            });
                            successCallback(formatted);
                        },
                        error: function(xhr) { console.warn('Events fetch failed:', xhr.status); successCallback([]); }
                    });
                },
                eventClick: function(info) {
                    var event = info.event;
                    var eventDate = event.start.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                    var type = (event.extendedProps && event.extendedProps.type) || 'Event';
                    var modalHtml = '<div class="modal fade" id="eventDetailModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">' +
                        '<div class="modal-header"><h5 class="modal-title">' + event.title + '</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>' +
                        '<div class="modal-body"><p><strong>Date:</strong> ' + eventDate + '</p>' +
                        '<p><strong>Type:</strong> <span class="badge bg-primary">' + type.charAt(0).toUpperCase() + type.slice(1) + '</span></p>' +
                        (event.extendedProps && event.extendedProps.description ? '<p><strong>Description:</strong><br>' + event.extendedProps.description + '</p>' : '') +
                        (event.extendedProps && event.extendedProps.venue ? '<p><strong>Venue:</strong> ' + event.extendedProps.venue + '</p>' : '') +
                        (event.extendedProps && event.extendedProps.time ? '<p><strong>Time:</strong> ' + event.extendedProps.time + '</p>' : '') +
                        '</div><div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button></div></div></div></div>';
                    $('#eventDetailModal').remove();
                    $('body').append(modalHtml);
                    new bootstrap.Modal(document.getElementById('eventDetailModal')).show();
                },
                dayMaxEvents: 2, height: 300, aspectRatio: 1.5,
                eventDidMount: function(info) {
                    var color = (info.event.extendedProps && info.event.extendedProps.type) ?
                        { academic:'#007bff',exam:'#dc3545',sports:'#28a745',cultural:'#ffc107',holiday:'#17a2b8',meeting:'#6c757d',submission:'#ffc107',workshop:'#17a2b8',event:'#007bff' }[info.event.extendedProps.type] || '#6c757d' : '#6c757d';
                    info.el.style.backgroundColor = color;
                    info.el.style.borderColor = color;
                }
            });
            studentCalendar.render();
        }
    @else
        // Admin calendar
        initMiniCalendar();
        
        // Load today's events
        loadEventsForDate('{{ date("Y-m-d") }}');
        
        // Date picker change
        $('#eventDatePicker').change(function() {
            var selectedDate = $(this).val();
            loadEventsForDate(selectedDate);
            
            // Update mini calendar to show selected date
            if (calendar) {
                calendar.gotoDate(selectedDate);
            }
        });
        
        // Today button
        $('#showTodayBtn').click(function() {
            var today = new Date().toISOString().split('T')[0];
            $('#eventDatePicker').val(today);
            loadEventsForDate(today);
            if (calendar) {
                calendar.today();
            }
        });
        
        // Quick event form - UPDATED for enhanced modal
        $('#quickEventForm').submit(function(e) {
            e.preventDefault();
            
            // Validate required fields
            var title = $(this).find('input[name="title"]').val();
            var eventDate = $(this).find('input[name="event_date"]').val();
            
            if (!title.trim()) {
                alert('Event title is required');
                return false;
            }
            
            if (!eventDate) {
                alert('Event date is required');
                return false;
            }
            
            // Show loading state
            var submitBtn = $(this).find('button[type="submit"]');
            var originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#addEventModal').modal('hide');
                    $('#quickEventForm')[0].reset();
                    $('#quickEventForm').find('input[name="event_date"]').val(new Date().toISOString().split('T')[0]);
                    
                    submitBtn.prop('disabled', false).html(originalText);
                    
                    // Show success popup
                    if (typeof PNotify !== 'undefined') {
                        new PNotify({
                            title: 'Event Added',
                            text: (response.event && response.event.title ? '"' + response.event.title + '"' : 'Event') + ' has been added successfully. It will appear for all users when they refresh their dashboard.',
                            type: 'success',
                            addclass: 'stack-bottom-right'
                        });
                    } else if (typeof toastr !== 'undefined') {
                        toastr.success('Event added successfully! It will appear for all users when they refresh.');
                    } else {
                        alert('Event added successfully! It will appear for all users when they refresh their dashboard.');
                    }
                    
                    // Reload events and calendar so it shows immediately for the admin who added it
                    if (calendar) {
                        calendar.refetchEvents();
                        // Navigate to the new event's date so it's visible
                        if (response.event && response.event.event_date) {
                            var eventDate = response.event.event_date.split('T')[0];
                            calendar.gotoDate(eventDate);
                            $('#eventDatePicker').val(eventDate);
                            loadEventsForDate(eventDate);
                        } else {
                            var currentDate = $('#eventDatePicker').val();
                            loadEventsForDate(currentDate);
                        }
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html(originalText);
                    
                    var errors = xhr.responseJSON.errors;
                    if (errors) {
                        var errorMsg = '';
                        $.each(errors, function(key, value) {
                            errorMsg += value[0] + '\n';
                        });
                        alert('Error: ' + errorMsg);
                    } else {
                        alert('Failed to add event. Please check console for details.');
                    }
                    console.error('Event add error:', xhr.responseText);
                }
            });
        });
    @endif

    var calendar;

    function initMiniCalendar() {
        var calendarEl = document.getElementById('miniCalendar');
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'title',
                center: '',
                right: 'prev,next'
            },
            height: 280,
            aspectRatio: 1.2,
            events: {
                url: '{{ route("events.get-by-range") }}',
                method: 'GET',
                extraParams: function() {
                    return {
                        start: calendar.view.activeStart.toISOString().split('T')[0],
                        end: calendar.view.activeEnd.toISOString().split('T')[0]
                    };
                }
            },
            eventClick: function(info) {
                // When user clicks an event, show details
                showEventDetails(info.event);
            },
            dateClick: function(info) {
                // When user clicks a date, load events for that date
                $('#eventDatePicker').val(info.dateStr);
                loadEventsForDate(info.dateStr);
            },
            eventDidMount: function(info) {
                // Style events based on type
                var color = getEventColor(info.event.extendedProps.event_type);
                info.el.style.backgroundColor = color;
                info.el.style.borderColor = color;
                info.el.style.fontSize = '0.7em';
            }
        });
        
        calendar.render();
    }

    function loadEventsForDate(date) {
        $('#dateEventsDisplay').html(`
            <div class="text-center py-2">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        
        $.ajax({
            url: '{{ route("events.get-by-date") }}',
            method: 'GET',
            data: { date: date },
            success: function(response) {
                if (response.length > 0) {
                    var html = '<div class="list-group list-group-flush">';
                    response.forEach(function(event) {
                        var badgeClass = getBadgeClass(event.event_type);
                        var timeDisplay = event.start_time ? 
                            (event.end_time ? event.start_time + ' - ' + event.end_time : event.start_time) : 
                            'All Day';
                        
                        html += `
                        <div class="list-group-item py-2 event-item" 
                             data-event-id="${event.id}" 
                             style="cursor: pointer; border-left: 3px solid ${getEventColor(event.event_type)}">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">${event.title}</h6>
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> ${timeDisplay}
                                        ${event.venue ? '<i class="fas fa-map-marker-alt ms-2"></i> ' + event.venue : ''}
                                    </small>
                                </div>
                                <span class="badge ${badgeClass}">${event.event_type}</span>
                            </div>
                            ${event.description ? '<small class="text-muted">' + event.description + '</small>' : ''}
                        </div>
                        `;
                    });
                    html += '</div>';
                } else {
                    var html = `
                    <div class="text-center py-3">
                        <i class="fas fa-calendar-times text-muted fa-2x mb-2"></i>
                        <p class="text-muted mb-0">No events scheduled</p>
                    </div>
                    `;
                }
                $('#dateEventsDisplay').html(html);
                
                // Add click handlers to event items
                $('.event-item').click(function() {
                    var eventId = $(this).data('event-id');
                    showEventDetailsById(eventId);
                });
            }
        });
    }

    function showEventDetails(event) {
        // Simple modal for event details
        var modalHtml = `
        <div class="modal fade" id="eventDetailModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${event.title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Date:</strong> ${event.start.toLocaleDateString()}</p>
                        ${event.extendedProps.description ? '<p>' + event.extendedProps.description + '</p>' : ''}
                        ${event.extendedProps.venue ? '<p><i class="fas fa-map-marker-alt"></i> ' + event.extendedProps.venue + '</p>' : ''}
                        <p><span class="badge ${getBadgeClass(event.extendedProps.event_type)}">${event.extendedProps.event_type}</span></p>
                    </div>
                </div>
            </div>
        </div>
        `;
        
        // Remove existing modal if any
        $('#eventDetailModal').remove();
        $('body').append(modalHtml);
        var modal = new bootstrap.Modal(document.getElementById('eventDetailModal'));
        modal.show();
    }

    function showEventDetailsById(eventId) {
        $.ajax({
            url: '/events/' + eventId,
            method: 'GET',
            success: function(event) {
                showEventDetails({
                    title: event.title,
                    start: new Date(event.event_date),
                    extendedProps: {
                        description: event.description,
                        venue: event.venue,
                        event_type: event.event_type
                    }
                });
            }
        });
    }

    function getBadgeClass(eventType) {
        var classes = {
            'academic': 'bg-primary',
            'exam': 'bg-danger',
            'sports': 'bg-success',
            'cultural': 'bg-warning',
            'holiday': 'bg-info',
            'meeting': 'bg-secondary',
            'submission': 'bg-warning',
            'workshop': 'bg-info',
            'event': 'bg-primary'
        };
        return classes[eventType] || 'bg-dark';
    }

    function getEventColor(eventType) {
        var colors = {
            'academic': '#007bff',
            'exam': '#dc3545',
            'sports': '#28a745',
            'cultural': '#ffc107',
            'holiday': '#17a2b8',
            'meeting': '#6c757d',
            'submission': '#ffc107',
            'workshop': '#17a2b8',
            'event': '#007bff'
        };
        return colors[eventType] || '#6c757d';
    }
});
</script>
@endpush