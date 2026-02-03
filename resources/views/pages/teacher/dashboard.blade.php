@extends('layouts.master')
@section('page_title', 'Teacher Dashboard')
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">
                        <i class="icon-user-tie"></i> Teacher Dashboard
                        <small class="float-right">Welcome, {{ Auth::user()->name }}!</small>
                    </h4>
                </div>
                <div class="card-body">
                    
                    <!-- Quick Stats Row -->
                    <div class="row mb-4">
                        <!-- My Subjects Card -->
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">{{ $my_subjects->count() ?? 0 }}</h3>
                                            <small>My Subjects</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="icon-book icon-2x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <small>Subjects I Teach</small>
                                </div>
                            </div>
                        </div>

                        <!-- Today's Classes -->
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">{{ count($today_timetable ?? []) }}</h3>
                                            <small>Today's Classes</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="icon-calendar icon-2x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <small>{{ \Carbon\Carbon::now()->format('l, F j, Y') }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- Upcoming Events -->
                        <div class="col-md-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">{{ $upcoming_events->count() ?? 0 }}</h3>
                                            <small>Upcoming Events</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="icon-calendar3 icon-2x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <small>Next 7 Days</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content Row -->
                    <div class="row">
                        <!-- Left Column: Today's Timetable & Marks Entry -->
                        <div class="col-lg-6">
                            <!-- Today's Schedule -->
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="icon-calendar"></i> Today's Teaching Schedule
                                        <span class="badge badge-light float-right">{{ \Carbon\Carbon::now()->format('l, F j') }}</span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if(isset($today_timetable) && count($today_timetable) > 0)
                                        <div class="list-group">
                                            @php
                                                $currentTime = \Carbon\Carbon::now();
                                            @endphp
                                            @foreach($today_timetable as $class)
                                                @php
                                                    $timeParts = explode(' - ', $class['time']);
                                                    $startTime = isset($timeParts[0]) ? Carbon::parse($timeParts[0]) : null;
                                                    $endTime = isset($timeParts[1]) ? Carbon::parse($timeParts[1]) : null;
                                                    
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
                                                <div class="list-group-item {{ $status === 'current' ? 'active' : '' }}">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <div>
                                                            <h6 class="mb-1">
                                                                <i class="icon-book text-primary"></i> {{ $class['subject'] }}
                                                                <small class="text-muted">({{ $class['class'] }})</small>
                                                            </h6>
                                                            <p class="mb-1">
                                                                <small><i class="icon-clock"></i> {{ $class['time'] }}</small>
                                                            </p>
                                                        </div>
                                                        <span class="badge {{ $badgeClass }}">
                                                            {{ ucfirst($status) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="icon-info"></i> No classes scheduled for today.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Marks Entry Quick Access -->
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="icon-pencil"></i> Marks Entry
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($my_subjects->count() > 0)
                                        <p class="text-muted">Quick access to enter marks for your subjects:</p>
                                        <div class="list-group">
                                            @foreach($my_subjects->take(5) as $subject)
                                                <a href="{{ route('marks.index') }}" class="list-group-item list-group-item-action">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <div>
                                                            <h6 class="mb-1">{{ $subject->name }}</h6>
                                                            <small class="text-muted">{{ $subject->my_class->name ?? 'N/A' }}</small>
                                                        </div>
                                                        <i class="icon-arrow-right7"></i>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                        @if($my_subjects->count() > 5)
                                            <div class="mt-2 text-center">
                                                <a href="{{ route('marks.index') }}" class="btn btn-sm btn-primary">
                                                    View All Subjects <i class="icon-arrow-right7"></i>
                                                </a>
                                            </div>
                                        @endif
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="icon-warning"></i> No subjects assigned to you yet.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Events & Weekly Timetable -->
                        <div class="col-lg-6">
                            <!-- Upcoming Events -->
                            <div class="card mb-4">
                                <div class="card-header bg-warning text-white">
                                    <h5 class="mb-0">
                                        <i class="icon-calendar3"></i> Upcoming Events
                                        <a href="{{ route('events.index') }}" class="float-right text-white">
                                            <small>View All <i class="icon-arrow-right7"></i></small>
                                        </a>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($upcoming_events && $upcoming_events->count() > 0)
                                        <div class="list-group">
                                            @foreach($upcoming_events->take(5) as $event)
                                                @php
                                                    $daysDiff = $event->event_date->diffInDays(\Carbon\Carbon::today());
                                                @endphp
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h6 class="mb-1">{{ $event->title }}</h6>
                                                            <small class="text-muted">
                                                                <i class="icon-calendar"></i> 
                                                                {{ $event->event_date->format('M j, Y') }}
                                                                @if($event->start_time)
                                                                    <i class="icon-clock ms-2"></i> 
                                                                    {{ date('h:i A', strtotime($event->start_time)) }}
                                                                @endif
                                                                @if($daysDiff == 0)
                                                                    <span class="badge badge-info ml-2">Today</span>
                                                                @elseif($daysDiff == 1)
                                                                    <span class="badge badge-warning ml-2">Tomorrow</span>
                                                                @endif
                                                            </small>
                                                        </div>
                                                        <span class="badge 
                                                            @if($event->event_type == 'holiday') badge-success
                                                            @elseif($event->event_type == 'exam') badge-danger
                                                            @elseif($event->event_type == 'academic') badge-primary
                                                            @else badge-info @endif">
                                                            {{ ucfirst($event->event_type) }}
                                                        </span>
                                                    </div>
                                                    @if($event->venue)
                                                        <small class="text-muted">
                                                            <i class="icon-location4"></i> {{ $event->venue }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="icon-info"></i> No upcoming events scheduled.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Weekly Timetable Overview -->
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="icon-calendar2"></i> Weekly Teaching Schedule
                                        </h5>
                                        <a href="{{ route('teacher.timetable_grid') }}" class="btn btn-sm btn-outline-primary mr-1">
                                            <i class="icon-grid6 mr-1"></i>Grid View
                                        </a>
                                        <a href="{{ route('teacher.weekly_timetable') }}" class="btn btn-sm btn-primary">
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
                                                <div class="list-group-item {{ $isToday ? 'active' : '' }}">
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
                                                                    {{ $class['time'] }} - {{ $class['subject'] }} ({{ $class['class'] }})
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
                    </div>

                    <!-- Quick Actions Row -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="icon-grid"></i> Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <a href="{{ route('marks.index') }}" class="btn btn-primary btn-block">
                                                <i class="icon-pencil"></i><br>
                                                Enter Marks
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="{{ route('tt.index') }}" class="btn btn-info btn-block">
                                                <i class="icon-calendar"></i><br>
                                                View Timetable
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="{{ route('events.index') }}" class="btn btn-warning btn-block">
                                                <i class="icon-calendar3"></i><br>
                                                School Events
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <button onclick="exportTimetableAndEvents()" class="btn btn-success btn-block">
                                                <i class="icon-file-excel"></i><br>
                                                Export Excel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Excel Export Script -->
<script>
function exportTimetableAndEvents() {
    // Show loading
    flash({msg: 'Preparing Excel export...', type: 'info'});
    
    // Redirect to export route
    window.location.href = '{{ route("teacher.export") }}';
}
</script>

@endsection
