{{-- Weekly timetable: time slots as rows, dates as columns. Uses timetable_slots (date-specific). --}}
@php
    $week_dates = $week_dates ?? [];
    $time_slots_array = $time_slots_array ?? [];
    $existing_weekly_slots = $existing_weekly_slots ?? [];
@endphp
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title font-weight-bold">
            <i class="icon-calendar52 mr-2"></i>Weekly Timetable (7:30 AM – 3:30 PM)
        </h6>
        <div class="header-elements d-flex align-items-center">
            <form method="get" action="{{ route('ttr.manage', $ttr_id) }}" class="d-inline-flex align-items-center mr-2">
                <input type="hidden" name="week_start" value="{{ \Carbon\Carbon::parse($week_start)->subWeek()->startOfWeek(\Carbon\Carbon::MONDAY)->format('Y-m-d') }}">
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="icon-arrow-left8 mr-1"></i>Prev week
                </button>
            </form>
            <span class="mx-2 text-muted">
                Week of {{ \Carbon\Carbon::parse($week_start)->format('M j, Y') }}
            </span>
            <form method="get" action="{{ route('ttr.manage', $ttr_id) }}" class="d-inline-flex align-items-center">
                <input type="hidden" name="week_start" value="{{ \Carbon\Carbon::parse($week_start)->addWeek()->startOfWeek(\Carbon\Carbon::MONDAY)->format('Y-m-d') }}">
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    Next week<i class="icon-arrow-right8 ml-1"></i>
                </button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="icon-info22 mr-2"></i>
            Assign <strong>subject</strong>, <strong>teacher</strong>, and <strong>classroom</strong> per date and time. Same classroom or teacher cannot be double-booked for the same date and time. Save when done.
        </div>

        <form method="post" action="{{ route('ttr.store_weekly_slots', $ttr_id) }}" id="weekly-timetable-form">
            @csrf
            <input type="hidden" name="week_start" value="{{ $week_start }}">
            <div class="table-responsive">
                <table class="table table-bordered timetable-weekly-grid">
                    <thead>
                        <tr>
                            <th class="bg-light" style="width: 120px;">Time</th>
                            @foreach($week_dates as $dateKey => $dateInfo)
                                <th class="text-center bg-light" style="min-width: 140px;">
                                    <strong>{{ $dateInfo['day_name'] }}</strong><br>
                                    <small class="text-muted">{{ $dateInfo['display'] }}</small>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($time_slots_array as $timeKey => $slotInfo)
                            <tr>
                                <td class="font-weight-semibold" style="background-color: #f8f9fa;">
                                    {{ $slotInfo['display'] }}
                                </td>
                                @foreach($week_dates as $dateKey => $dateInfo)
                                    @php
                                        $slot = $existing_weekly_slots[$dateKey][$timeKey] ?? null;
                                    @endphp
                                    <td class="weekly-cell" data-date="{{ $dateKey }}" data-time="{{ $timeKey }}">
                                        <div class="slot-fields p-1">
                                            <input type="hidden" name="slots[{{ $dateKey }}][{{ $timeKey }}][date]" value="{{ $dateKey }}">
                                            <input type="hidden" name="slots[{{ $dateKey }}][{{ $timeKey }}][time]" value="{{ $timeKey }}">
                                            <div class="form-group mb-1">
                                                <select name="slots[{{ $dateKey }}][{{ $timeKey }}][subject_id]" class="form-control form-control-sm subject-select">
                                                    <option value="">Subject</option>
                                                    @foreach($subjects as $s)
                                                        <option value="{{ $s->id }}" {{ $slot && $slot->subject_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group mb-1">
                                                <select name="slots[{{ $dateKey }}][{{ $timeKey }}][teacher_id]" class="form-control form-control-sm teacher-select">
                                                    <option value="">Teacher</option>
                                                    @foreach($teachers as $t)
                                                        <option value="{{ $t->id }}" {{ $slot && $slot->teacher_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group mb-1">
                                                <select name="slots[{{ $dateKey }}][{{ $timeKey }}][classroom_id]" class="form-control form-control-sm classroom-select">
                                                    <option value="">Room</option>
                                                    @foreach($classrooms as $r)
                                                        <option value="{{ $r->id }}" {{ $slot && $slot->classroom_id == $r->id ? 'selected' : '' }}>{{ $r->room_number }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <input type="text" name="slots[{{ $dateKey }}][{{ $timeKey }}][notes]" class="form-control form-control-sm" placeholder="Notes" value="{{ $slot && $slot->notes ? $slot->notes : '' }}">
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary" id="weekly-save-btn">
                    <i class="icon-checkmark3 mr-2"></i>Save Weekly Slots
                </button>
                <button type="button" class="btn btn-secondary" id="weekly-clear-btn">Clear all in this week</button>
            </div>
        </form>
    </div>
</div>

<style>
.timetable-weekly-grid { font-size: 0.8rem; }
.timetable-weekly-grid th, .timetable-weekly-grid td { vertical-align: top; }
.weekly-cell { min-height: 120px; }
.slot-fields .form-control-sm { height: 28px; padding: 2px 6px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('weekly-timetable-form');
    var saveBtn = document.getElementById('weekly-save-btn');
    var clearBtn = document.getElementById('weekly-clear-btn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            if (confirm('Clear all subject/teacher/room/notes in this week?')) {
                form.querySelectorAll('.subject-select, .teacher-select, .classroom-select').forEach(function(el) { el.value = ''; });
                form.querySelectorAll('input[name*="[notes]"]').forEach(function(el) { el.value = ''; });
            }
        });
    }
});
</script>
