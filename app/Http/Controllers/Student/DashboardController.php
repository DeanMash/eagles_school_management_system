<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Helpers\Qs;
use App\Repositories\UserRepo;
use App\Repositories\TimeTableRepo;
use App\Repositories\StudentRepo;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $user, $tt, $student;
    
    public function __construct(UserRepo $user, TimeTableRepo $tt, StudentRepo $student)
    {
        $this->user = $user;
        $this->tt = $tt;
        $this->student = $student;
        $this->middleware('auth');
    }

    public function index()
    {
        $d = [];
        
        // Get student record
        $studentRecord = $this->student->getRecord(['user_id' => Auth::id()])->first();
        
        if ($studentRecord) {
            $classId = $studentRecord->my_class_id;
            $sectionId = $studentRecord->section_id;
            
            // Get timetable record for this class
            $ttRecord = \App\Models\TimeTableRecord::where('my_class_id', $classId)
                ->whereNull('exam_id') // Regular timetable, not exam timetable
                ->first();
            
            if ($ttRecord) {
                // Get today's day name in different formats
                $todayDayName = Carbon::now()->format('l'); // e.g., "Monday"
                $todayDayShort = Carbon::now()->format('D'); // e.g., "Mon"
                $todayDayNumber = Carbon::now()->dayOfWeekIso; // 1-7 (Monday-Sunday)
                
                // Get time slots for this timetable
                $timeSlots = $this->tt->getTimeSlotByTTR($ttRecord->id);
                
                // Get all timetable entries for this class
                $allTimetable = $this->tt->getTimeTable(['ttr_id' => $ttRecord->id]);
                
                // Filter today's timetable entries (try different day formats)
                $todayTimetable = $allTimetable->filter(function($entry) use ($todayDayName, $todayDayShort, $todayDayNumber) {
                    $entryDay = $entry->day ?? '';
                    return $entryDay == $todayDayName || 
                           $entryDay == $todayDayShort || 
                           $entryDay == $todayDayNumber ||
                           strtolower($entryDay) == strtolower($todayDayName) ||
                           strtolower($entryDay) == strtolower($todayDayShort);
                });
                
                // Organize timetable by time slot
                $d['today_timetable'] = [];
                foreach ($timeSlots as $slot) {
                    $ttEntry = $todayTimetable->where('ts_id', $slot->id)->first();
                    if ($ttEntry && $ttEntry->subject) {
                        $timeStr = $slot->full ?? 
                                  ($slot->timestamp_from && $slot->timestamp_to ? 
                                   date('h:i A', strtotime($slot->timestamp_from)) . ' - ' . date('h:i A', strtotime($slot->timestamp_to)) : 
                                   'N/A');
                        $d['today_timetable'][] = [
                            'time' => $timeStr,
                            'subject' => $ttEntry->subject->name ?? 'N/A',
                            'subject_id' => $ttEntry->subject_id ?? null,
                            'time_slot' => $slot,
                            'tt_entry' => $ttEntry
                        ];
                    }
                }
                
                // Sort today's timetable by time
                usort($d['today_timetable'], function($a, $b) {
                    $timeA = $a['time_slot']->timestamp_from ?? '';
                    $timeB = $b['time_slot']->timestamp_from ?? '';
                    return strcmp($timeA, $timeB);
                });
                
                // Get weekly timetable (all days) - group by day name
                $d['weekly_timetable'] = $allTimetable->groupBy(function($item) {
                    return $item->day ?? 'Unknown';
                });
                $d['time_slots'] = $timeSlots;
            } else {
                $d['today_timetable'] = [];
                $d['weekly_timetable'] = collect();
            }
            
            $d['student_record'] = $studentRecord;
            $d['class_name'] = $studentRecord->my_class->name ?? 'N/A';
        }
        
        // Get upcoming events (next 30 days, public events)
        $today = Carbon::today();
        $nextMonth = Carbon::today()->addDays(30);
        
        $d['upcoming_events'] = Event::where('is_public', true)
            ->whereBetween('event_date', [$today, $nextMonth])
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->limit(10)
            ->get();
        
        // Get today's events
        $d['today_events'] = Event::where('is_public', true)
            ->whereDate('event_date', $today)
            ->orderBy('start_time')
            ->get();
        
        if(Qs::userIsTeamSAT()){
            $d['users'] = $this->user->getAll();
        }

        return view('pages.support_team.dashboard', $d);
    }

    public function timetable_grid()
    {
        $d = [];
        $studentRecord = $this->student->getRecord(['user_id' => Auth::id()])->first();
        
        if (!$studentRecord) {
            return redirect()->route('student.dashboard')->with('flash_danger', 'Student record not found.');
        }

        $classId = $studentRecord->my_class_id;
        $ttRecord = \App\Models\TimeTableRecord::where('my_class_id', $classId)
            ->whereNull('exam_id')
            ->first();

        if (!$ttRecord) {
            return redirect()->route('student.dashboard')->with('flash_danger', 'Timetable not found for your class.');
        }

        $d['ttr_id'] = $ttRecord->id;
        $d['ttr'] = $ttRecord;
        $d['time_slots'] = $this->tt->getTimeSlotByTTR($ttRecord->id)->sortBy('timestamp_from');
        $d['my_class'] = $studentRecord->my_class;
        
        // Get all subjects for the class (students see all subjects)
        $myClassRepo = app(\App\Repositories\MyClassRepo::class);
        $d['subjects'] = $myClassRepo->getSubject(['my_class_id' => $classId])->get();
        
        // Get existing timetable entries grouped by day and time slot
        $d['tts'] = $this->tt->getTimeTable(['ttr_id' => $ttRecord->id]);
        $d['timetable_grid'] = [];
        
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        foreach ($daysOfWeek as $day) {
            $d['timetable_grid'][$day] = [];
            foreach ($d['time_slots'] as $slot) {
                $entry = $d['tts']->where('day', $day)->where('ts_id', $slot->id)->first();
                $d['timetable_grid'][$day][$slot->id] = $entry ? [
                    'id' => $entry->id,
                    'subject_id' => $entry->subject_id,
                    'subject_name' => $entry->subject->name ?? null
                ] : null;
            }
        }

        $d['readonly'] = true;
        return view('pages.support_team.timetables.grid', $d);
    }

    /**
     * Weekly timetable view: dates as columns, time slots as rows.
     * Uses timetable_slots for the selected week; falls back to recurring time_tables.
     */
    public function weekly_timetable(\Illuminate\Http\Request $request)
    {
        $studentRecord = $this->student->getRecord(['user_id' => Auth::id()])->first();
        if (!$studentRecord) {
            return redirect()->route('student.dashboard')->with('flash_danger', 'Student record not found.');
        }

        $ttRecord = \App\Models\TimeTableRecord::where('my_class_id', $studentRecord->my_class_id)
            ->whereNull('exam_id')
            ->first();
        if (!$ttRecord) {
            return redirect()->route('student.dashboard')->with('flash_danger', 'Timetable not found for your class.');
        }

        $weekStart = $request->get('week_start', Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'));
        $weekStart = Carbon::parse($weekStart)->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(6);

        $weekDates = $ttRecord->getWeekDates($weekStart->format('Y-m-d'));
        $timeSlotsArray = \App\Models\TimeTableRecord::getTimeSlotsArray();

        $slots = \App\Models\TimetableSlot::where('ttr_id', $ttRecord->id)
            ->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->with(['subject', 'teacher', 'classroom'])
            ->get();
        $organizedSlots = [];
        foreach ($slots as $s) {
            $dateKey = Carbon::parse($s->date)->format('Y-m-d');
            $timeKey = Carbon::parse($s->start_time)->format('H:i');
            if (!isset($organizedSlots[$dateKey])) {
                $organizedSlots[$dateKey] = [];
            }
            $organizedSlots[$dateKey][$timeKey] = $s;
        }

        $recurring = [];
        $timeSlots = $this->tt->getTimeSlotByTTR($ttRecord->id);
        $tts = $this->tt->getTimeTable(['ttr_id' => $ttRecord->id]);
        foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
            $recurring[$day] = [];
            foreach ($timeSlots as $ts) {
                $timeKey = date('H:i', strtotime($ts->time_from));
                $entry = $tts->where('day', $day)->where('ts_id', $ts->id)->first();
                $recurring[$day][$timeKey] = $entry ? [
                    'subject_name' => $entry->subject->name ?? null,
                    'teacher_name' => $entry->subject && $entry->subject->teacher ? $entry->subject->teacher->name : null,
                    'classroom_name' => $entry->classroom ? $entry->classroom->room_number : null,
                ] : null;
            }
        }

        return view('pages.timetable.weekly', [
            'page_title' => 'My Weekly Timetable',
            'timetable_grid_route' => route('student.timetable_grid'),
            'week_start' => $weekStart->format('Y-m-d'),
            'week_dates' => $weekDates,
            'time_slots_array' => $timeSlotsArray,
            'organizedSlots' => $organizedSlots,
            'recurring' => $recurring,
        ]);
    }
}