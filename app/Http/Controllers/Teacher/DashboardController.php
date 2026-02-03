<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Helpers\Qs;
use App\Repositories\UserRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\TimeTableRepo;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $user, $my_class, $tt;
    
    public function __construct(UserRepo $user, MyClassRepo $my_class, TimeTableRepo $tt)
    {
        $this->user = $user;
        $this->my_class = $my_class;
        $this->tt = $tt;
        $this->middleware('auth');
    }

    public function index()
    {
        $d = [];
        $teacherId = Auth::id();
        
        // Get teacher's subjects
        $d['my_subjects'] = $this->my_class->findSubjectByTeacher($teacherId);
        
        // Get today's day name
        $todayDayName = Carbon::now()->format('l'); // e.g., "Monday"
        $todayDayShort = Carbon::now()->format('D'); // e.g., "Mon"
        
        // Get teacher's timetable for today
        $d['today_timetable'] = [];
        $d['weekly_timetable'] = collect();
        
        if ($d['my_subjects']->count() > 0) {
            // Get all timetable records
            $allTtRecords = \App\Models\TimeTableRecord::whereNull('exam_id')->get();
            
            foreach ($allTtRecords as $ttRecord) {
                // Get timetable entries for this record
                $timetableEntries = $this->tt->getTimeTable(['ttr_id' => $ttRecord->id]);
                
                // Filter entries that match teacher's subjects
                $teacherEntries = $timetableEntries->filter(function($entry) use ($teacherId) {
                    return $entry->subject && $entry->subject->teacher_id == $teacherId;
                });
                
                if ($teacherEntries->count() > 0) {
                    // Get today's entries
                    $todayEntries = $teacherEntries->filter(function($entry) use ($todayDayName, $todayDayShort) {
                        $entryDay = $entry->day ?? '';
                        return $entryDay == $todayDayName || 
                               $entryDay == $todayDayShort ||
                               strtolower($entryDay) == strtolower($todayDayName) ||
                               strtolower($entryDay) == strtolower($todayDayShort);
                    });
                    
                    // Get time slots
                    $timeSlots = $this->tt->getTimeSlotByTTR($ttRecord->id);
                    
                    foreach ($todayEntries as $entry) {
                        $slot = $timeSlots->where('id', $entry->ts_id)->first();
                        if ($slot) {
                            $timeStr = $slot->full ?? 
                                      ($slot->timestamp_from && $slot->timestamp_to ? 
                                       date('h:i A', strtotime($slot->timestamp_from)) . ' - ' . date('h:i A', strtotime($slot->timestamp_to)) : 
                                       'N/A');
                            $d['today_timetable'][] = [
                                'time' => $timeStr,
                                'subject' => $entry->subject->name ?? 'N/A',
                                'subject_id' => $entry->subject_id,
                                'class' => $ttRecord->my_class->name ?? 'N/A',
                                'class_id' => $ttRecord->my_class_id,
                                'time_slot' => $slot,
                                'tt_entry' => $entry
                            ];
                        }
                    }
                    
                    // Group weekly timetable by day
                    foreach ($teacherEntries->groupBy('day') as $day => $entries) {
                        if (!isset($d['weekly_timetable'][$day])) {
                            $d['weekly_timetable'][$day] = collect();
                        }
                        foreach ($entries as $entry) {
                            $slot = $timeSlots->where('id', $entry->ts_id)->first();
                            $d['weekly_timetable'][$day]->push([
                                'time' => $slot ? ($slot->full ?? 'N/A') : 'N/A',
                                'subject' => $entry->subject->name ?? 'N/A',
                                'class' => $ttRecord->my_class->name ?? 'N/A',
                            ]);
                        }
                    }
                }
            }
            
            // Sort today's timetable by time
            usort($d['today_timetable'], function($a, $b) {
                $timeA = $a['time_slot']->timestamp_from ?? '';
                $timeB = $b['time_slot']->timestamp_from ?? '';
                return strcmp($timeA, $timeB);
            });
        }
        
        // Get upcoming events (next 7 days, public events)
        $today = Carbon::today();
        $nextWeek = Carbon::today()->addDays(7);
        
        $d['upcoming_events'] = Event::where('is_public', true)
            ->whereBetween('event_date', [$today, $nextWeek])
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->limit(10)
            ->get();
        
        // Get today's events
        $d['today_events'] = Event::where('is_public', true)
            ->whereDate('event_date', $today)
            ->orderBy('start_time')
            ->get();
        
        // Get classes the teacher teaches
        $d['my_classes'] = $d['my_subjects']->pluck('my_class_id')->unique()->map(function($classId) {
            return \App\Models\MyClass::find($classId);
        })->filter();
        
        if(Qs::userIsTeamSAT()){
            $d['users'] = $this->user->getAll();
        }

        return view('pages.teacher.dashboard', $d);
    }

    public function timetable_grid()
    {
        $d = [];
        $teacherId = Auth::id();
        
        // Get teacher's subjects
        $mySubjects = $this->my_class->findSubjectByTeacher($teacherId);
        
        if ($mySubjects->count() == 0) {
            return redirect()->route('teacher.dashboard')->with('flash_danger', 'No subjects assigned. Please contact administration.');
        }

        // Get all classes the teacher teaches
        $classIds = $mySubjects->pluck('my_class_id')->unique();
        
        // Get timetable records for these classes
        $ttRecords = \App\Models\TimeTableRecord::whereIn('my_class_id', $classIds)
            ->whereNull('exam_id')
            ->get();

        if ($ttRecords->count() == 0) {
            return redirect()->route('teacher.dashboard')->with('flash_danger', 'No timetables found for your classes.');
        }

        // Use the first timetable (or allow selection if multiple)
        $ttRecord = $ttRecords->first();
        
        $d['ttr_id'] = $ttRecord->id;
        $d['ttr'] = $ttRecord;
        $d['time_slots'] = $this->tt->getTimeSlotByTTR($ttRecord->id)->sortBy('timestamp_from');
        $d['my_class'] = $ttRecord->my_class;
        
        // Filter subjects to only show teacher's subjects
        $d['subjects'] = $mySubjects->where('my_class_id', $ttRecord->my_class_id);
        
        // Get existing timetable entries grouped by day and time slot
        $d['tts'] = $this->tt->getTimeTable(['ttr_id' => $ttRecord->id])
            ->filter(function($entry) use ($teacherId) {
                return $entry->subject && $entry->subject->teacher_id == $teacherId;
            });
        
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
     * Weekly timetable view for teacher: dates as columns, time slots as rows.
     * Uses timetable_slots where teacher_id = auth for the selected week; falls back to recurring.
     */
    public function weekly_timetable(\Illuminate\Http\Request $request)
    {
        $teacherId = Auth::id();
        $mySubjects = $this->my_class->findSubjectByTeacher($teacherId);
        if ($mySubjects->count() == 0) {
            return redirect()->route('teacher.dashboard')->with('flash_danger', 'No subjects assigned.');
        }

        $classIds = $mySubjects->pluck('my_class_id')->unique();
        $ttRecords = \App\Models\TimeTableRecord::whereIn('my_class_id', $classIds)->whereNull('exam_id')->get();
        if ($ttRecords->count() == 0) {
            return redirect()->route('teacher.dashboard')->with('flash_danger', 'No timetables found for your classes.');
        }

        $weekStart = $request->get('week_start', Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'));
        $weekStart = Carbon::parse($weekStart)->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(6);

        $ttr = $ttRecords->first();
        $weekDates = $ttr->getWeekDates($weekStart->format('Y-m-d'));
        $timeSlotsArray = \App\Models\TimeTableRecord::getTimeSlotsArray();

        $slots = \App\Models\TimetableSlot::where('teacher_id', $teacherId)
            ->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->with(['subject', 'classroom', 'tt_record.my_class'])
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
        $timeSlots = $this->tt->getTimeSlotByTTR($ttr->id);
        $tts = $this->tt->getTimeTable(['ttr_id' => $ttr->id])
            ->filter(function ($entry) use ($teacherId) {
                return $entry->subject && $entry->subject->teacher_id == $teacherId;
            });
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
            'page_title' => 'My Teaching Schedule (Weekly)',
            'timetable_grid_route' => route('teacher.timetable_grid'),
            'week_start' => $weekStart->format('Y-m-d'),
            'week_dates' => $weekDates,
            'time_slots_array' => $timeSlotsArray,
            'organizedSlots' => $organizedSlots,
            'recurring' => $recurring,
        ]);
    }
}