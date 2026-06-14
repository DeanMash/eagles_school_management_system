<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Requests\TimeTable\TSRequest;
use App\Http\Requests\TimeTable\TTRecordRequest;
use App\Http\Requests\TimeTable\TTRequest;
use App\Models\Setting;
use App\Repositories\ExamRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\TimeTableRepo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TimeTableController extends Controller
{
    protected $tt, $my_class, $exam, $year;

    public function __construct(TimeTableRepo $tt, MyClassRepo $mc, ExamRepo $exam)
    {
        $this->middleware('teamSA', ['except' => ['index', 'show_record', 'print_record']]);

        $this->tt = $tt;
        $this->my_class = $mc;
        $this->exam = $exam;
        $this->year = Qs::getCurrentSession();
    }

    public function index()
    {
        $d['exams'] = $this->exam->getExam(['year' => $this->year]);
        $d['my_classes'] = $this->my_class->all();
        $d['tt_records'] = $this->tt->getAllRecords();

        return view('pages.support_team.timetables.index', $d);
    }

    public function manage($ttr_id)
    {
        try {
            $d['ttr_id'] = $ttr_id;
            $d['ttr'] = $ttr = $this->tt->findRecord($ttr_id);
            
            if (!$ttr) {
                abort(404, 'Timetable record not found');
            }
            
            $d['time_slots'] = $this->tt->getTimeSlotByTTR($ttr_id)->sortBy('timestamp_from');
            $d['ts_existing'] = $this->tt->getExistingTS($ttr_id);
            $d['subjects'] = $this->my_class->getSubject(['my_class_id' => $ttr->my_class_id])->get();
            $d['my_class'] = $this->my_class->find($ttr->my_class_id);
            
            if (!$d['my_class']) {
                abort(404, 'Class not found for this timetable');
            }

            if($ttr->exam_id){
                $d['exam_id'] = $ttr->exam_id;
                $d['exam'] = $this->exam->find($ttr->exam_id);
            }

            $d['tts'] = $this->tt->getTimeTable(['ttr_id' => $ttr_id]);
            $d['classrooms'] = \App\Models\Classroom::orderBy('room_number')->get();
            
            // Build timetable_grid for grid view (same as grid_view method)
            $d['timetable_grid'] = [];
            $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            foreach ($daysOfWeek as $day) {
                $d['timetable_grid'][$day] = [];
                foreach ($d['time_slots'] as $slot) {
                    $entry = $d['tts']->where('day', $day)->where('ts_id', $slot->id)->first();
                    $d['timetable_grid'][$day][$slot->id] = $entry ? [
                        'id' => $entry->id,
                        'subject_id' => $entry->subject_id,
                        'subject_name' => $entry->subject->name ?? null,
                        'classroom_id' => $entry->classroom_id ?? null,
                        'classroom_name' => $entry->classroom->room_number ?? null,
                        'teacher_name' => $entry->subject && $entry->subject->teacher ? $entry->subject->teacher->name : null,
                    ] : null;
                }
            }

            // Weekly view: dates (horizontal) and time slots (vertical)
            $weekStart = request('week_start', \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY)->format('Y-m-d'));
            $d['week_start'] = $weekStart;
            $d['week_dates'] = $ttr->getWeekDates($weekStart);
            $d['time_slots_array'] = \App\Models\TimeTableRecord::getTimeSlotsArray();
            $d['teachers'] = \App\Models\User::where('user_type', 'teacher')->orderBy('name')->get();
            $startDate = \Carbon\Carbon::parse($weekStart);
            $endDate = $startDate->copy()->addDays(6);
            $weeklySlots = \App\Models\TimetableSlot::where('ttr_id', $ttr_id)
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->with(['subject', 'teacher', 'classroom'])
                ->get();
            $d['existing_weekly_slots'] = [];
            foreach ($weeklySlots as $s) {
                $dateKey = $s->date->format('Y-m-d');
                $timeKey = \Carbon\Carbon::parse($s->start_time)->format('H:i');
                if (!isset($d['existing_weekly_slots'][$dateKey])) {
                    $d['existing_weekly_slots'][$dateKey] = [];
                }
                $d['existing_weekly_slots'][$dateKey][$timeKey] = $s;
            }

            return view('pages.support_team.timetables.manage', $d);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Timetable record not found');
        } catch (\Exception $e) {
            \Log::error('Error in manage method: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            abort(500, 'An error occurred while loading the timetable: ' . $e->getMessage());
        }
    }

    public function grid_view($ttr_id)
    {
        $d['ttr_id'] = $ttr_id;
        $d['ttr'] = $ttr = $this->tt->findRecord($ttr_id);
        $d['time_slots'] = $this->tt->getTimeSlotByTTR($ttr_id)->sortBy('timestamp_from');
        $d['subjects'] = $this->my_class->getSubject(['my_class_id' => $ttr->my_class_id])->get();
        $d['my_class'] = $this->my_class->find($ttr->my_class_id);
        
        // Get existing timetable entries grouped by day and time slot
        $d['tts'] = $this->tt->getTimeTable(['ttr_id' => $ttr_id]);
        $d['classrooms'] = \App\Models\Classroom::orderBy('room_number')->get();
        $d['timetable_grid'] = [];
        
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        foreach ($daysOfWeek as $day) {
            $d['timetable_grid'][$day] = [];
            foreach ($d['time_slots'] as $slot) {
                $entry = $d['tts']->where('day', $day)->where('ts_id', $slot->id)->first();
                $d['timetable_grid'][$day][$slot->id] = $entry ? [
                    'id' => $entry->id,
                    'subject_id' => $entry->subject_id,
                    'subject_name' => $entry->subject->name ?? null,
                    'classroom_id' => $entry->classroom_id ?? null,
                    'classroom_name' => $entry->classroom->room_number ?? null,
                    'teacher_name' => $entry->subject && $entry->subject->teacher ? $entry->subject->teacher->name : null,
                ] : null;
            }
        }

        return view('pages.support_team.timetables.grid', $d);
    }

    public function bulk_store_subjects(Request $req, $ttr_id)
    {
        try {
            $assignments = $req->input('assignments', []);
            $created = 0;
            $updated = 0;
            $deleted = 0;

            // Handle both array formats: indexed array or associative array
            if (!is_numeric(key($assignments))) {
                // Convert associative array to indexed array
                $assignments = array_values($assignments);
            }

            foreach ($assignments as $assignment) {
                $day = $assignment['day'] ?? null;
                $ts_id = isset($assignment['ts_id']) ? (int) $assignment['ts_id'] : null;
                $subject_id = isset($assignment['subject_id']) && $assignment['subject_id'] ? (int) $assignment['subject_id'] : null;
                $entry_id = isset($assignment['entry_id']) && $assignment['entry_id'] ? (int) $assignment['entry_id'] : null;

                if (!$day || !$ts_id) {
                    continue;
                }

                // Get time slot for timestamp calculation
                $timeSlot = $this->tt->findTimeSlot($ts_id);
                if (!$timeSlot) {
                    \Log::warning("Time slot {$ts_id} not found");
                    continue;
                }

                // Check if entry exists (by entry_id or by day+slot)
                $existing = null;
                if ($entry_id) {
                    $existing = \App\Models\TimeTable::where('id', $entry_id)
                        ->where('ttr_id', $ttr_id)
                        ->first();
                }
                
                if (!$existing) {
                    $existing = \App\Models\TimeTable::where('ttr_id', $ttr_id)
                        ->where('day', $day)
                        ->where('ts_id', $ts_id)
                        ->first();
                }

                if ($subject_id) {
                    // Create or update entry
                    $classroom_id = isset($assignment['classroom_id']) && $assignment['classroom_id'] ? (int) $assignment['classroom_id'] : null;
                $subject = \App\Models\Subject::find($subject_id);
                $teacher_id = $subject ? $subject->teacher_id : null;

                // Conflict: same classroom double-booked (same ttr, day, slot)
                if ($classroom_id) {
                    $classroomConflict = \App\Models\TimeTable::where('ttr_id', $ttr_id)
                        ->where('day', $day)
                        ->where('classroom_id', $classroom_id)
                        ->where('ts_id', $ts_id)
                        ->when($entry_id, function ($q) use ($entry_id) { return $q->where('id', '!=', $entry_id); })
                        ->exists();
                    if ($classroomConflict) {
                        \Log::warning("Classroom conflict: room already used for this slot");
                        continue;
                    }
                }
                // Conflict: same teacher double-booked (same ttr, day, slot)
                if ($teacher_id) {
                    $teacherConflict = \App\Models\TimeTable::where('ttr_id', $ttr_id)
                        ->where('day', $day)
                        ->where('ts_id', $ts_id)
                        ->when($entry_id, function ($q) use ($entry_id) { return $q->where('id', '!=', $entry_id); })
                        ->whereHas('subject', function ($q) use ($teacher_id) { $q->where('teacher_id', $teacher_id); })
                        ->exists();
                    if ($teacherConflict) {
                        \Log::warning("Teacher conflict: teacher already assigned for this slot");
                        continue;
                    }
                }

                $data = [
                        'ttr_id' => (int) $ttr_id,
                        'ts_id' => $ts_id,
                        'day' => $day,
                        'subject_id' => $subject_id,
                        'classroom_id' => $classroom_id,
                        'timestamp_from' => (string) strtotime($day . ' ' . $timeSlot->time_from),
                        'timestamp_to' => (string) strtotime($day . ' ' . $timeSlot->time_to),
                    ];

                    if ($existing) {
                        $this->tt->update($existing->id, $data);
                        $updated++;
                    } else {
                        $this->tt->create($data);
                        $created++;
                    }
                } else {
                    // Delete entry if subject_id is empty
                    if ($existing) {
                        $this->tt->delete($existing->id);
                        $deleted++;
                    }
                }
            }

            return response()->json([
                'ok' => true,
                'msg' => "Timetable updated successfully. Created: {$created}, Updated: {$updated}, Deleted: {$deleted}."
            ], 200)->header('Content-Type', 'application/json');

        } catch (\Exception $e) {
            \Log::error('Bulk timetable update error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'ok' => false,
                'msg' => 'Error updating timetable: ' . $e->getMessage()
            ], 200)->header('Content-Type', 'application/json');
        }
    }

    public function store(TTRequest $req)
    {
        $data = $req->all();
        $tms = $this->tt->findTimeSlot($req->ts_id);
        $d_date = $req->exam_date ?? $req->day;
        $data['timestamp_from'] = strtotime($d_date.' '.$tms->time_from);
        $data['timestamp_to'] = strtotime($d_date.' '.$tms->time_to);

        $this->tt->create($data);

        return Qs::jsonStoreOk();
    }

    public function update(TTRequest $req, $tt_id)
    {
        $data = $req->all();
        $tms = $this->tt->findTimeSlot($req->ts_id);
        $d_date = $req->exam_date ?? $req->day;
        $data['timestamp_from'] = strtotime($d_date.' '.$tms->time_from);
        $data['timestamp_to'] = strtotime($d_date.' '.$tms->time_to);

        $this->tt->update($tt_id, $data);

        return back()->with('flash_success', __('msg.update_ok'));

    }

    public function delete($tt_id)
    {
        $this->tt->delete($tt_id);
        return back()->with('flash_success', __('msg.delete_ok'));
    }

    /*********** TIME SLOTS *************/

    public function store_time_slot(TSRequest $req)
    {
        $data = $req->all();
        $data['time_from'] = $tf =$req->hour_from.':'.$req->min_from.' '.$req->meridian_from;
        $data['time_to'] = $tt = $req->hour_to.':'.$req->min_to.' '.$req->meridian_to;
        $data['timestamp_from'] = strtotime($tf);
        $data['timestamp_to'] = strtotime($tt);
        $data['full'] = $tf.' - '.$tt;

        if($tf == $tt){
            return response()->json(['msg' => __('msg.invalid_time_slot'), 'ok' => FALSE]);
        }

        $this->tt->createTimeSlot($data);
        return Qs::jsonStoreOk();
    }

    public function use_time_slot(Request $req, $ttr_id)
    {
        $this->validate($req, ['ttr_id' => 'required'], [], ['ttr_id' => 'TimeTable Record']);

        $d = [];  //  Empty Current Time Slot Before Adding New
        $this->tt->deleteTimeSlots(['ttr_id' => $ttr_id]);
        $time_slots = $this->tt->getTimeSlotByTTR($req->ttr_id)->toArray();

        foreach($time_slots as $ts){
            $ts['ttr_id'] = $ttr_id;
            $this->tt->createTimeSlot($ts);
        }

        return redirect()->route('ttr.manage', $ttr_id)->with('flash_success', __('msg.update_ok'));

    }

    public function edit_time_slot($ts_id)
    {
        $d['tms'] = $this->tt->findTimeSlot($ts_id);
        return view('pages.support_team.timetables.time_slots.edit', $d);
    }

    public function update_time_slot(TSRequest $req, $ts_id)
    {
        $data = $req->all();
        $data['time_from'] = $tf =$req->hour_from.':'.$req->min_from.' '.$req->meridian_from;
        $data['time_to'] = $tt = $req->hour_to.':'.$req->min_to.' '.$req->meridian_to;
        $data['timestamp_from'] = strtotime($tf);
        $data['timestamp_to'] = strtotime($tt);
        $data['full'] = $tf.' - '.$tt;

        if($tf == $tt){
            return back()->with('flash_danger', __('msg.invalid_time_slot'));
        }

        $this->tt->updateTimeSlot($ts_id, $data);
        return redirect()->route('ttr.manage', $req->ttr_id)->with('flash_success', __('msg.update_ok'));
    }

    public function delete_time_slot($ts_id)
    {
        $this->tt->deleteTimeSlot($ts_id);
        return back()->with('flash_success', __('msg.delete_ok'));
    }

    public function bulk_create_time_slots(Request $req, $ttr_id)
    {
        try {
            $ttr_id = (int) $ttr_id;
            $ttr = $this->tt->findRecord($ttr_id);
            if (!$ttr) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Timetable record not found.'
                ], 200)->header('Content-Type', 'application/json');
            }

            // Delete existing time slots for this timetable
            $this->tt->deleteTimeSlots(['ttr_id' => $ttr_id]);

            // Generate 30-minute slots from 7:30 AM to 3:30 PM (24h: 7.5 to 15.5)
            // Break: 10:00 AM - 10:30 AM (skip)
            // Lunch: 2:00 PM - 2:30 PM (skip)
            $slots = [];
            $start = 7 * 60 + 30;   // 7:30 = 450 minutes from midnight
            $end = 15 * 60 + 30;    // 15:30 = 930 minutes
            $skipRanges = [
                [10 * 60, 10 * 60 + 30],      // 10:00 - 10:30
                [14 * 60, 14 * 60 + 30],       // 14:00 - 14:30
            ];

            for ($mins = $start; $mins + 30 <= $end; $mins += 30) {
                $endMins = $mins + 30;
                $skip = false;
                foreach ($skipRanges as $range) {
                    if (($mins >= $range[0] && $mins < $range[1]) || ($endMins > $range[0] && $endMins <= $range[1])) {
                        $skip = true;
                        break;
                    }
                }
                if ($skip) {
                    continue;
                }

                $h1 = (int) floor($mins / 60);
                $m1 = $mins % 60;
                $h2 = (int) floor($endMins / 60);
                $m2 = $endMins % 60;

                $hourFrom = $h1 > 12 ? $h1 - 12 : ($h1 == 0 ? 12 : $h1);
                $hourTo = $h2 > 12 ? $h2 - 12 : ($h2 == 0 ? 12 : $h2);
                $meridianFrom = $h1 < 12 ? 'AM' : 'PM';
                $meridianTo = $h2 < 12 ? 'AM' : 'PM';
                $minFrom = str_pad((string) $m1, 2, '0', STR_PAD_LEFT);
                $minTo = str_pad((string) $m2, 2, '0', STR_PAD_LEFT);

                $timeFrom = $hourFrom . ':' . $minFrom . ' ' . $meridianFrom;
                $timeTo = $hourTo . ':' . $minTo . ' ' . $meridianTo;
                $tsFrom = mktime($h1, $m1, 0, 1, 1, date('Y'));
                $tsTo = mktime($h2, $m2, 0, 1, 1, date('Y'));

                $slotData = [
                    'ttr_id' => $ttr_id,
                    'hour_from' => (int) $hourFrom,
                    'min_from' => $minFrom,
                    'meridian_from' => $meridianFrom,
                    'hour_to' => (int) $hourTo,
                    'min_to' => $minTo,
                    'meridian_to' => $meridianTo,
                    'time_from' => $timeFrom,
                    'time_to' => $timeTo,
                    'timestamp_from' => (string) $tsFrom,
                    'timestamp_to' => (string) $tsTo,
                    'full' => $timeFrom . ' - ' . $timeTo
                ];
                $slots[] = $slotData;
            }

            $created = 0;
            foreach ($slots as $slot) {
                try {
                    $this->tt->createTimeSlot($slot);
                    $created++;
                } catch (\Exception $e) {
                    \Log::error('Bulk create single slot error: ' . $e->getMessage(), ['slot' => $slot]);
                }
            }

            $msg = "Successfully created {$created} time slots (7:30 AM to 3:30 PM, 30-min intervals).";

            // Form POST: redirect back to manage page with success message
            if (!$req->wantsJson() && !$req->ajax()) {
                return redirect()->route('ttr.manage', $ttr_id)->with('flash_success', $msg);
            }

            return response()->json([
                'ok' => true,
                'msg' => $msg
            ], 200)->header('Content-Type', 'application/json');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if (!$req->wantsJson() && !$req->ajax()) {
                return redirect()->route('ttr.manage', $ttr_id)->with('flash_danger', 'Timetable record not found.');
            }
            return response()->json([
                'ok' => false,
                'msg' => 'Timetable record not found.'
            ], 200)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            \Log::error('Bulk time slot creation error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            if (!$req->wantsJson() && !$req->ajax()) {
                return redirect()->route('ttr.manage', $ttr_id)->with('flash_danger', 'Error creating time slots: ' . $e->getMessage());
            }
            return response()->json([
                'ok' => false,
                'msg' => 'Error creating time slots. ' . $e->getMessage()
            ], 200)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Store weekly timetable slots (date-specific). Used by the Weekly View tab.
     */
    public function store_weekly_slots(Request $req, $ttr_id)
    {
        $req->validate([
            'week_start' => 'required|date',
            'slots' => 'present|array',
        ]);

        $ttr_id = (int) $ttr_id;
        $ttr = $this->tt->findRecord($ttr_id);
        if (!$ttr) {
            return redirect()->route('ttr.manage', $ttr_id)->with('flash_danger', 'Timetable record not found.');
        }

        $weekStart = \Carbon\Carbon::parse($req->week_start)->startOfWeek(\Carbon\Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(6);
        $startStr = $weekStart->format('Y-m-d');
        $endStr = $weekEnd->format('Y-m-d');

        // Collect all (date, start_time) we will have after this save
        $toCreate = [];
        $errors = [];

        foreach ($req->input('slots', []) as $date => $daySlots) {
            if (!is_array($daySlots)) {
                continue;
            }
            $dateObj = \Carbon\Carbon::parse($date);
            if ($dateObj->lt($weekStart) || $dateObj->gt($weekEnd)) {
                continue;
            }
            foreach ($daySlots as $timeKey => $slotData) {
                if (!is_array($slotData)) {
                    continue;
                }
                $subjectId = isset($slotData['subject_id']) && $slotData['subject_id'] ? (int) $slotData['subject_id'] : null;
                $teacherId = isset($slotData['teacher_id']) && $slotData['teacher_id'] ? (int) $slotData['teacher_id'] : null;
                $classroomId = isset($slotData['classroom_id']) && $slotData['classroom_id'] ? (int) $slotData['classroom_id'] : null;
                $notes = isset($slotData['notes']) ? trim($slotData['notes']) : null;

                if (!$subjectId || !$teacherId) {
                    continue;
                }

                $startTime = \Carbon\Carbon::createFromFormat('H:i', $timeKey);
                $endTime = $startTime->copy()->addMinutes(30);
                $toCreate[] = [
                    'date' => $date,
                    'start_time' => $startTime->format('H:i:s'),
                    'end_time' => $endTime->format('H:i:s'),
                    'subject_id' => $subjectId,
                    'teacher_id' => $teacherId,
                    'classroom_id' => $classroomId ?: null,
                    'notes' => $notes ?: null,
                ];
            }
        }

        // Remove existing slots for this timetable in this week
        \App\Models\TimetableSlot::where('ttr_id', $ttr_id)
            ->whereBetween('date', [$startStr, $endStr])
            ->delete();

        $created = 0;
        foreach ($toCreate as $data) {
            try {
                \App\Models\TimetableSlot::create([
                    'ttr_id' => $ttr_id,
                    'date' => $data['date'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'subject_id' => $data['subject_id'],
                    'teacher_id' => $data['teacher_id'],
                    'classroom_id' => $data['classroom_id'],
                    'notes' => $data['notes'],
                ]);
                $created++;
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() == '23000' || strpos($e->getMessage(), 'Duplicate') !== false) {
                    $errors[] = 'Conflict at ' . $data['date'] . ' ' . $data['start_time'] . ' (classroom or teacher already used).';
                } else {
                    $errors[] = $e->getMessage();
                }
            }
        }

        $msg = "Weekly slots saved. Created/updated {$created} slot(s).";
        if (count($errors) > 0) {
            $msg .= ' Some conflicts: ' . implode(' ', array_slice($errors, 0, 3));
        }

        return redirect()->to(route('ttr.manage', $ttr_id) . '?week_start=' . $weekStart->format('Y-m-d'))
            ->with($errors ? 'flash_warning' : 'flash_success', $msg);
    }

    /*********** RECORDS *************/

    public function edit_record($ttr_id)
    {
        $d['ttr'] = $ttr = $this->tt->findRecord($ttr_id);
        $d['exams'] = $this->exam->getExam(['year' => $ttr->year]);
        $d['my_classes'] = $this->my_class->all();

        return view('pages.support_team.timetables.edit', $d);
    }

    public function show_record($ttr_id)
    {
        $d_time = [];
        $d['ttr'] = $ttr = $this->tt->findRecord($ttr_id);
        $d['ttr_id'] = $ttr_id;
        $d['my_class'] = $this->my_class->find($ttr->my_class_id);

        $d['time_slots'] = $tms = $this->tt->getTimeSlotByTTR($ttr_id);
        $d['tts'] = $tts = $this->tt->getTimeTable(['ttr_id' => $ttr_id]);

        if($ttr->exam_id){
            $d['exam_id'] = $ttr->exam_id;
            $d['exam'] = $this->exam->find($ttr->exam_id);
            $d['days'] = $days = $tts->unique('exam_date')->pluck('exam_date');
            $d_date = 'exam_date';
        }

        else{
            $d['days'] = $days = $tts->unique('day')->pluck('day');
            $d_date = 'day';
        }

        foreach ($days as $day) {
            foreach ($tms as $tm) {
                $d_time[] = ['day' => $day, 'time' => $tm->full, 'subject' => $tts->where('ts_id', $tm->id)->where($d_date, $day)->first()->subject->name ?? NULL ];
            }
        }

        $d['d_time'] = collect($d_time);

        return view('pages.support_team.timetables.show', $d);
    }
    public function print_record($ttr_id)
    {
        $d_time = [];
        $d['ttr'] = $ttr = $this->tt->findRecord($ttr_id);
        $d['ttr_id'] = $ttr_id;
        $d['my_class'] = $this->my_class->find($ttr->my_class_id);

        $d['time_slots'] = $tms = $this->tt->getTimeSlotByTTR($ttr_id);
        $d['tts'] = $tts = $this->tt->getTimeTable(['ttr_id' => $ttr_id]);

        if($ttr->exam_id){
            $d['exam_id'] = $ttr->exam_id;
            $d['exam'] = $this->exam->find($ttr->exam_id);
            $d['days'] = $days = $tts->unique('exam_date')->pluck('exam_date');
            $d_date = 'exam_date';
        }

        else{
            $d['days'] = $days = $tts->unique('day')->pluck('day');
            $d_date = 'day';
        }

        foreach ($days as $day) {
            foreach ($tms as $tm) {
                $d_time[] = ['day' => $day, 'time' => $tm->full, 'subject' => $tts->where('ts_id', $tm->id)->where($d_date, $day)->first()->subject->name ?? NULL ];
            }
        }

        $d['d_time'] = collect($d_time);
        $d['s'] = Setting::all()->flatMap(function($s){
            return [$s->type => $s->description];
        });

        return view('pages.support_team.timetables.print', $d);
    }

    public function store_record(TTRecordRequest $req)
    {
        try {
            $data = $req->all();
            $data['year'] = $this->year;
            
            \Log::info('Creating timetable record with data:', $data);
            
            $record = $this->tt->createRecord($data);

            if (!$record || !$record->id) {
                \Log::error('Failed to create timetable record - record is null or has no ID');
                return response()->json([
                    'ok' => false,
                    'msg' => 'Failed to create timetable record. Please try again.'
                ], 200);
            }

            \Log::info('Timetable record created successfully:', ['id' => $record->id, 'name' => $record->name]);

            // Return explicit success response with redirect so address bar updates
            $response = [
                'ok' => true,
                'msg' => __('msg.store_ok') . ' - ' . $record->name,
                'redirect_url' => route('ttr.manage', $record->id),
            ];
            \Log::info('Returning success response:', $response);

            return response()->json($response, 200)
                ->header('Content-Type', 'application/json')
                ->header('Cache-Control', 'no-cache, must-revalidate');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Timetable validation failed:', $e->errors());
            $errorMsg = 'Validation failed: ' . implode(', ', array_map(function($errors) {
                return is_array($errors) ? implode(', ', $errors) : $errors;
            }, array_values($e->errors())));
            return response()->json([
                'ok' => false,
                'msg' => $errorMsg,
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error creating timetable: ' . $e->getMessage());
            \Log::error('SQL State: ' . $e->getCode());
            
            $errorMsg = 'Database error. ';
            if (strpos($e->getMessage(), 'Duplicate entry') !== false || strpos($e->getMessage(), '1062') !== false) {
                $errorMsg .= 'A timetable with this name already exists for this year.';
            } else if (strpos($e->getMessage(), '1452') !== false) {
                $errorMsg .= 'Invalid class or exam selected.';
            } else {
                $errorMsg .= 'Please check your input and try again.';
            }
            return response()->json([
                'ok' => false,
                'msg' => $errorMsg
            ], 200)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            \Log::error('Timetable creation error: ' . $e->getMessage());
            \Log::error('Error class: ' . get_class($e));
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'ok' => false,
                'msg' => 'An error occurred: ' . (app()->environment('local') ? $e->getMessage() : 'Please contact the administrator.')
            ], 200)->header('Content-Type', 'application/json');
        }
    }

    public function update_record(TTRecordRequest $req, $ttr_id)
    {
        $data = $req->all();
        $this->tt->updateRecord($ttr_id, $data);

        return Qs::jsonUpdateOk();
    }

    public function delete_record($ttr_id)
    {
        $this->tt->deleteRecord($ttr_id);
        return back()->with('flash_success', __('msg.delete_ok'));
    }
}
