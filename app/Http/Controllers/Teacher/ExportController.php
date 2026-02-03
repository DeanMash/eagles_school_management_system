<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Helpers\Qs;
use App\Repositories\MyClassRepo;
use App\Repositories\TimeTableRepo;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExportController extends Controller
{
    protected $my_class, $tt;

    public function __construct(MyClassRepo $my_class, TimeTableRepo $tt)
    {
        $this->middleware('auth');
        $this->middleware('teacher');
        $this->my_class = $my_class;
        $this->tt = $tt;
    }

    public function export()
    {
        $teacherId = Auth::id();
        $teacher = Auth::user();
        
        // Get teacher's subjects
        $subjects = $this->my_class->findSubjectByTeacher($teacherId);
        
        // Get today's day name
        $todayDayName = Carbon::now()->format('l');
        $todayDayShort = Carbon::now()->format('D');
        
        // Get teacher's timetable for today
        $todayTimetable = [];
        $allTtRecords = \App\Models\TimeTableRecord::whereNull('exam_id')->get();
        
        foreach ($allTtRecords as $ttRecord) {
            $timetableEntries = $this->tt->getTimeTable(['ttr_id' => $ttRecord->id]);
            $teacherEntries = $timetableEntries->filter(function($entry) use ($teacherId) {
                return $entry->subject && $entry->subject->teacher_id == $teacherId;
            });
            
            if ($teacherEntries->count() > 0) {
                $todayEntries = $teacherEntries->filter(function($entry) use ($todayDayName, $todayDayShort) {
                    $entryDay = $entry->day ?? '';
                    return $entryDay == $todayDayName || 
                           $entryDay == $todayDayShort ||
                           strtolower($entryDay) == strtolower($todayDayName) ||
                           strtolower($entryDay) == strtolower($todayDayShort);
                });
                
                $timeSlots = $this->tt->getTimeSlotByTTR($ttRecord->id);
                
                foreach ($todayEntries as $entry) {
                    $slot = $timeSlots->where('id', $entry->ts_id)->first();
                    if ($slot) {
                        $timeStr = $slot->full ?? 
                                  ($slot->timestamp_from && $slot->timestamp_to ? 
                                   date('h:i A', strtotime($slot->timestamp_from)) . ' - ' . date('h:i A', strtotime($slot->timestamp_to)) : 
                                   'N/A');
                        $todayTimetable[] = [
                            'time' => $timeStr,
                            'subject' => $entry->subject->name ?? 'N/A',
                            'class' => $ttRecord->my_class->name ?? 'N/A',
                        ];
                    }
                }
            }
        }
        
        // Get today's events
        $today = Carbon::today();
        $todayEvents = Event::where('is_public', true)
            ->whereDate('event_date', $today)
            ->orderBy('start_time')
            ->get();
        
        // Get upcoming events (next 7 days)
        $nextWeek = Carbon::today()->addDays(7);
        $upcomingEvents = Event::where('is_public', true)
            ->whereBetween('event_date', [$today, $nextWeek])
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();
        
        // Generate Excel/CSV content
        $filename = 'Teacher_Dashboard_' . $teacher->name . '_' . Carbon::now()->format('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header
        fputcsv($output, ['TEACHER DASHBOARD EXPORT']);
        fputcsv($output, ['Teacher:', $teacher->name]);
        fputcsv($output, ['Date:', Carbon::now()->format('F j, Y')]);
        fputcsv($output, []); // Empty row
        
        // Today's Timetable Section
        fputcsv($output, ['TODAY\'S TEACHING SCHEDULE']);
        fputcsv($output, ['Time', 'Subject', 'Class']);
        foreach ($todayTimetable as $class) {
            fputcsv($output, [
                $class['time'],
                $class['subject'],
                $class['class']
            ]);
        }
        fputcsv($output, []); // Empty row
        
        // Today's Events Section
        fputcsv($output, ['TODAY\'S EVENTS']);
        fputcsv($output, ['Title', 'Time', 'Venue', 'Type']);
        foreach ($todayEvents as $event) {
            fputcsv($output, [
                $event->title,
                $event->start_time ? date('h:i A', strtotime($event->start_time)) : 'N/A',
                $event->venue ?? 'N/A',
                ucfirst($event->event_type)
            ]);
        }
        fputcsv($output, []); // Empty row
        
        // Upcoming Events Section
        fputcsv($output, ['UPCOMING EVENTS (Next 7 Days)']);
        fputcsv($output, ['Title', 'Date', 'Time', 'Venue', 'Type']);
        foreach ($upcomingEvents as $event) {
            fputcsv($output, [
                $event->title,
                $event->event_date->format('M j, Y'),
                $event->start_time ? date('h:i A', strtotime($event->start_time)) : 'N/A',
                $event->venue ?? 'N/A',
                ucfirst($event->event_type)
            ]);
        }
        fputcsv($output, []); // Empty row
        
        // My Subjects Section
        fputcsv($output, ['MY SUBJECTS']);
        fputcsv($output, ['Subject Name', 'Class']);
        foreach ($subjects as $subject) {
            fputcsv($output, [
                $subject->name,
                $subject->my_class->name ?? 'N/A'
            ]);
        }
        
        fclose($output);
        exit;
    }
}
