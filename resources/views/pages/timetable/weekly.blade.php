@extends('layouts.master')
@section('page_title', $page_title ?? 'Weekly Timetable')
@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">{{ $page_title ?? 'Weekly Timetable' }}</h6>
            <div class="header-elements d-flex align-items-center">
                <a href="{{ $timetable_grid_route }}" class="btn btn-sm btn-outline-secondary mr-2">Recurring view</a>
                <form method="get" action="{{ Request::url() }}" class="d-inline-flex align-items-center mr-2">
                    <input type="hidden" name="week_start" value="{{ \Carbon\Carbon::parse($week_start)->subWeek()->startOfWeek(\Carbon\Carbon::MONDAY)->format('Y-m-d') }}">
                    <button type="submit" class="btn btn-sm btn-outline-primary">← Prev week</button>
                </form>
                <span class="mx-2 text-muted">Week of {{ \Carbon\Carbon::parse($week_start)->format('M j, Y') }}</span>
                <form method="get" action="{{ Request::url() }}" class="d-inline-flex align-items-center">
                    <input type="hidden" name="week_start" value="{{ \Carbon\Carbon::parse($week_start)->addWeek()->startOfWeek(\Carbon\Carbon::MONDAY)->format('Y-m-d') }}">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Next week →</button>
                </form>
            </div>
        </div>
        <div class="card-body">
            @php
                $week_dates = $week_dates ?? [];
                $time_slots_array = $time_slots_array ?? [];
                $organizedSlots = $organizedSlots ?? [];
                $recurring = $recurring ?? [];
            @endphp
            @if(empty($week_dates) || empty($time_slots_array))
                <p class="text-muted">No timetable data available.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered timetable-weekly-readonly">
                        <thead>
                            <tr>
                                <th class="bg-light" style="width: 120px;">Time</th>
                                @foreach($week_dates as $dateKey => $dateInfo)
                                    <th class="text-center {{ $dateInfo['is_today'] ?? false ? 'today-header' : 'bg-light' }}" style="min-width: 130px;">
                                        <strong>{{ $dateInfo['day_name'] }}</strong><br>
                                        <small class="text-muted">{{ $dateInfo['display'] }}</small>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($time_slots_array as $timeKey => $slotInfo)
                                <tr>
                                    <td class="font-weight-semibold" style="background-color: #f8f9fa;">{{ $slotInfo['display'] }}</td>
                                    @foreach($week_dates as $dateKey => $dateInfo)
                                        @php
                                            $slot = $organizedSlots[$dateKey][$timeKey] ?? null;
                                            $dayName = $dateInfo['day_name'] ?? '';
                                            $recurringEntry = $recurring[$dayName][$timeKey] ?? null;
                                            $display = $slot ?? $recurringEntry;
                                        @endphp
                                        <td class="weekly-cell {{ $dateInfo['is_today'] ?? false ? 'today-cell' : '' }}">
                                            @if($display)
                                                @if($slot)
                                                    <div class="p-2 small">
                                                        <strong>{{ $slot->subject->name ?? '' }}</strong>
                                                        @if($slot->teacher)
                                                            <br><span class="text-muted">{{ $slot->teacher->name }}</span>
                                                        @endif
                                                        @if($slot->classroom)
                                                            <br><span class="text-muted">Room: {{ $slot->classroom->room_number }}</span>
                                                        @endif
                                                        @if($slot->notes)
                                                            <br><span class="text-muted">{{ $slot->notes }}</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="p-2 small">
                                                        <strong>{{ $recurringEntry['subject_name'] ?? '' }}</strong>
                                                        @if(!empty($recurringEntry['teacher_name']))
                                                            <br><span class="text-muted">{{ $recurringEntry['teacher_name'] }}</span>
                                                        @endif
                                                        @if(!empty($recurringEntry['classroom_name']))
                                                            <br><span class="text-muted">Room: {{ $recurringEntry['classroom_name'] }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            @else
                                                <div class="p-2 text-muted small">—</div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
<style>
.timetable-weekly-readonly { font-size: 0.85rem; }
.timetable-weekly-readonly th, .timetable-weekly-readonly td { vertical-align: top; }
.weekly-cell { min-height: 70px; }
.today-header { background-color: #cce5ff !important; }
.today-cell { background-color: #e6f2ff !important; }
</style>
@endsection
