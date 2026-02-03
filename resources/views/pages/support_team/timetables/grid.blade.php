@if(!isset($readonly))
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title font-weight-bold">
            <i class="icon-grid6 mr-2"></i>Timetable Grid - {{ $ttr->name }} ({{ $my_class->name }})
        </h6>
        <div class="header-elements">
            <a href="{{ route('ttr.show', $ttr_id) }}" target="_blank" class="btn btn-sm btn-info">
                <i class="icon-eye mr-2"></i>View Timetable
            </a>
        </div>
    </div>
@else
<div class="card">
    <div class="card-header header-elements-inline bg-info">
        <h6 class="card-title font-weight-bold text-white">
            <i class="icon-calendar52 mr-2"></i>My Timetable - {{ $ttr->name }} ({{ $my_class->name }})
        </h6>
    </div>
@endif

    <div class="card-body">
        @if($time_slots->count() == 0)
            <div class="alert alert-warning">
                <i class="icon-warning22 mr-2"></i>
                No time slots found. Please create time slots first using the "Quick Create Time Slots" button.
            </div>
        @elseif($subjects->count() == 0)
            <div class="alert alert-warning">
                <i class="icon-warning22 mr-2"></i>
                No subjects found for this class. Please add subjects first.
            </div>
        @else
            <div class="alert alert-info">
                <i class="icon-info22 mr-2"></i>
                <strong>Weekly timetable (7:30 AM – 3:30 PM):</strong> Columns = days, rows = 30‑min time slots. Assign subject and room per cell; click "Save Timetable" when done.
            </div>

            <form id="timetable-grid-form" method="post" action="{{ route('ttr.bulk_store_subjects', $ttr_id) }}">
                @csrf
                
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="table table-bordered table-hover timetable-grid" style="min-width: 1200px;">
                        <thead>
                            <tr>
                                <th class="bg-primary text-white" style="width: 130px; position: sticky; left: 0; z-index: 10;">
                                    <strong>Time</strong>
                                </th>
                                @php $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']; @endphp
                                @foreach($daysOfWeek as $day)
                                    <th class="bg-light text-center" style="min-width: 140px;">
                                        <strong>{{ $day }}</strong>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($time_slots as $slot)
                                <tr>
                                    <td class="bg-primary text-white font-weight-bold" style="position: sticky; left: 0; z-index: 5;">
                                        <div class="font-weight-semibold">{{ $slot->full }}</div>
                                        <small>{{ date('H:i', strtotime($slot->time_from)) }} – {{ date('H:i', strtotime($slot->time_to)) }}</small>
                                    </td>
                                    @foreach($daysOfWeek as $day)
                                        @php
                                            $entry = $timetable_grid[$day][$slot->id] ?? null;
                                        @endphp
                                        <td class="timetable-cell {{ isset($readonly) ? 'readonly-cell' : '' }}" data-day="{{ $day }}" data-slot="{{ $slot->id }}" data-entry-id="{{ $entry['id'] ?? '' }}">
                                            @if(isset($readonly))
                                                @if($entry && $entry['subject_name'])
                                                    <div class="text-center p-2 bg-primary text-white rounded">
                                                        <strong>{{ $entry['subject_name'] }}</strong>
                                                        @if(!empty($entry['teacher_name']))
                                                            <br><small>{{ $entry['teacher_name'] }}</small>
                                                        @endif
                                                        @if(!empty($entry['classroom_name']))
                                                            <br><small>Room: {{ $entry['classroom_name'] }}</small>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="text-center p-2 text-muted">
                                                        <em>Free</em>
                                                    </div>
                                                @endif
                                            @else
                                                <select class="form-control form-control-sm subject-select" 
                                                        name="assignments[{{ $day }}_{{ $slot->id }}][subject_id]"
                                                        data-day="{{ $day }}"
                                                        data-slot-id="{{ $slot->id }}"
                                                        data-entry-id="{{ $entry['id'] ?? '' }}">
                                                    <option value="">-- Select Subject --</option>
                                                    @foreach($subjects as $subject)
                                                        <option value="{{ $subject->id }}" 
                                                                {{ ($entry && $entry['subject_id'] == $subject->id) ? 'selected' : '' }}>
                                                            {{ $subject->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if(isset($classrooms) && $classrooms->count() > 0)
                                                <select class="form-control form-control-sm mt-1" name="assignments[{{ $day }}_{{ $slot->id }}][classroom_id]">
                                                    <option value="">-- Room (optional) --</option>
                                                    @foreach($classrooms as $room)
                                                        <option value="{{ $room->id }}" {{ ($entry && ($entry['classroom_id'] ?? null) == $room->id) ? 'selected' : '' }}>{{ $room->room_number }}{{ $room->building ? ' (' . $room->building . ')' : '' }}</option>
                                                    @endforeach
                                                </select>
                                                @endif
                                                <input type="hidden" name="assignments[{{ $day }}_{{ $slot->id }}][day]" value="{{ $day }}">
                                                <input type="hidden" name="assignments[{{ $day }}_{{ $slot->id }}][ts_id]" value="{{ $slot->id }}">
                                                <input type="hidden" name="assignments[{{ $day }}_{{ $slot->id }}][entry_id]" value="{{ $entry['id'] ?? '' }}">
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-right mt-3">
                    <button type="submit" id="save-timetable-btn" class="btn btn-primary btn-lg">
                        <i class="icon-checkmark3 mr-2"></i>Save Timetable
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>

<style>
.timetable-grid {
    font-size: 0.875rem;
}
.timetable-grid thead th {
    vertical-align: middle;
    padding: 10px 5px;
}
.timetable-grid tbody td {
    vertical-align: middle;
    padding: 8px 5px;
}
.timetable-cell {
    background-color: #f9f9f9;
    transition: background-color 0.2s;
}
.timetable-cell:hover {
    background-color: #f0f0f0;
}
.subject-select {
    font-size: 0.8rem;
    padding: 4px 8px;
    height: auto;
}
.subject-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
.readonly-cell {
    background-color: #ffffff;
}
.readonly-cell:hover {
    background-color: #f8f9fa;
}
</style>

@if(!isset($readonly))
<script>
$(document).ready(function() {
    var form = $('#timetable-grid-form');
    var $saveBtn = $('#save-timetable-btn');
    var originalBtnHtml = $saveBtn.html();
    
    form.on('submit', function(e) {
        e.preventDefault();
        
        // Collect all assignments (subject + optional classroom)
        var assignments = [];
        $('.subject-select').each(function() {
            var $select = $(this);
            var $cell = $select.closest('td');
            var day = $select.data('day');
            var slotId = $select.data('slot-id');
            var entryId = $select.data('entry-id') || '';
            var subjectId = $select.val() || null;
            var classroomId = $cell.find('select[name*="[classroom_id]"]').val() || null;
            
            assignments.push({
                day: day,
                ts_id: slotId,
                subject_id: subjectId,
                entry_id: entryId,
                classroom_id: classroomId
            });
        });
        
        // Disable button
        $saveBtn.prop('disabled', true).html('<i class="icon-spinner spinner mr-2"></i>Saving...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                assignments: assignments
            },
            dataType: 'json',
            success: function(resp) {
                if (resp.ok) {
                    flash({msg: resp.msg, type: 'success'});
                    // Reload page after 1 second to show updated assignments
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    flash({msg: resp.msg, type: 'danger'});
                    $saveBtn.prop('disabled', false).html(originalBtnHtml);
                }
            },
            error: function(xhr) {
                var errorMsg = 'Error saving timetable.';
                if (xhr.responseJSON && xhr.responseJSON.msg) {
                    errorMsg = xhr.responseJSON.msg;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                flash({msg: errorMsg, type: 'danger'});
                $saveBtn.prop('disabled', false).html(originalBtnHtml);
            }
        });
    });
});
</script>
@endif

</div>
