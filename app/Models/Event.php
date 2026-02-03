<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'venue',
        'event_type',
        'created_by',
        'is_public',
        'repeat',
        'repeat_until',
        'class_id',
        'section_id'
    ];

    protected $casts = [
        'event_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_public' => 'boolean',
        'repeat_until' => 'date'
    ];

    protected $appends = [
        'badge_class',
        'color',
        'is_today',
        'time_range'
    ];

    // Relationship with creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship with class
    public function class()
    {
        return $this->belongsTo(MyClass::class, 'class_id');
    }

    // Relationship with section
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    // Scope for public events
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to only include today's events.
     */
    public function scopeToday(Builder $query)
    {
        return $query->whereDate('event_date', today());
    }

    /**
     * Scope a query to only include upcoming events.
     */
    public function scopeUpcoming(Builder $query, $limit = null)
    {
        $query = $query->whereDate('event_date', '>=', today())
                       ->orderBy('event_date', 'asc')
                       ->orderBy('start_time', 'asc');
        
        if ($limit) {
            $query = $query->limit($limit);
        }
        
        return $query;
    }

    // Scope for events on a specific date
    public function scopeOnDate($query, $date)
    {
        return $query->where('event_date', $date);
    }

    // Scope for events within a date range
    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('event_date', [$start, $end]);
    }

    // Get events for a specific student (based on their class/section)
    public function scopeForStudent($query, $studentId)
    {
        // Get student's class and section
        $student = User::find($studentId);
        
        if ($student && $student->studentRecord) {
            $classId = $student->studentRecord->my_class_id;
            $sectionId = $student->studentRecord->section_id;
            
            return $query->where(function($q) use ($classId, $sectionId) {
                // Events for all students (no class/section specified)
                $q->whereNull('class_id')
                  ->whereNull('section_id')
                  // OR events for student's class (all sections)
                  ->orWhere('class_id', $classId)
                  ->whereNull('section_id')
                  // OR events for student's specific class and section
                  ->orWhere(function($q2) use ($classId, $sectionId) {
                      $q2->where('class_id', $classId)
                         ->where('section_id', $sectionId);
                  });
            });
        }
        
        return $query->whereNull('class_id')->whereNull('section_id');
    }

    /**
     * Scope a query to only include events for a specific user type.
     */
    public function scopeForUser($query, $userId)
    {
        $user = User::find($userId);
        
        if (!$user) {
            return $query->public();
        }
        
        switch ($user->user_type) {
            case 'student':
                return $query->forStudent($userId);
                
            case 'teacher':
                // Teachers can see all public events + events for their classes
                return $query->where(function($q) use ($user) {
                    $q->where('is_public', true)
                      ->orWhere('created_by', $user->id);
                    // Add logic for teacher's classes if needed
                });
                
            case 'admin':
            case 'super_admin':
                // Admins can see all events
                return $query;
                
            default:
                return $query->public();
        }
    }

    /**
     * Scope a query to only include events for calendar display.
     */
    public function scopeForCalendar($query, $userId = null)
    {
        if ($userId) {
            $query = $query->forUser($userId);
        } else {
            $query = $query->public();
        }
        
        return $query->select([
            'id', 'title', 'description', 'event_date', 
            'start_time', 'end_time', 'venue', 'event_type',
            'is_public', 'class_id', 'section_id'
        ]);
    }

    /**
     * Format event for FullCalendar
     */
    public function toCalendarEvent()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->event_date->format('Y-m-d') . 
                      ($this->start_time ? 'T' . $this->start_time->format('H:i:s') : ''),
            'end' => $this->end_time ? 
                     $this->event_date->format('Y-m-d') . 'T' . $this->end_time->format('H:i:s') : 
                     $this->event_date->format('Y-m-d') . 'T23:59:59',
            'allDay' => !$this->start_time && !$this->end_time,
            'color' => $this->color,
            'textColor' => '#ffffff',
            'extendedProps' => [
                'description' => $this->description,
                'venue' => $this->venue,
                'event_type' => $this->event_type,
                'time_range' => $this->time_range,
                'is_public' => $this->is_public,
                'badge_class' => $this->badge_class
            ]
        ];
    }

    /**
     * Get upcoming events with days count
     */
    public function getDaysUntilAttribute()
    {
        return now()->diffInDays($this->event_date, false);
    }

    /**
     * Get formatted date with day name
     */
    public function getFormattedDateAttribute()
    {
        return $this->event_date->format('D, M j, Y');
    }

    /**
     * Get events by month
     */
    public function scopeByMonth($query, $year = null, $month = null)
    {
        $year = $year ?? date('Y');
        $month = $month ?? date('m');
        
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));
        
        return $query->whereBetween('event_date', [$startDate, $endDate])
                     ->orderBy('event_date')
                     ->orderBy('start_time');
    }

    // Get badge class based on event type
    public function getBadgeClassAttribute()
    {
        $classes = [
            'academic' => 'bg-primary',
            'exam' => 'bg-danger',
            'sports' => 'bg-success',
            'cultural' => 'bg-warning',
            'holiday' => 'bg-info',
            'meeting' => 'bg-secondary',
            'submission' => 'bg-warning',
            'workshop' => 'bg-info',
            'event' => 'bg-primary'
        ];
        
        return $classes[$this->event_type] ?? 'bg-dark';
    }

    // Get color based on event type
    public function getColorAttribute()
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
            'event' => '#007bff'
        ];
        
        return $colors[$this->event_type] ?? '#6c757d';
    }

    // Check if event is happening today
    public function getIsTodayAttribute()
    {
        return $this->event_date->isToday();
    }

    // Format time range
    public function getTimeRangeAttribute()
    {
        if ($this->start_time && $this->end_time) {
            return date('h:i A', strtotime($this->start_time)) . ' - ' . 
                   date('h:i A', strtotime($this->end_time));
        } elseif ($this->start_time) {
            return 'Starts at ' . date('h:i A', strtotime($this->start_time));
        } else {
            return 'All Day';
        }
    }

    /**
     * Get events for dashboard display
     */
    public static function getDashboardEvents($userId = null, $limit = 5)
    {
        $query = self::upcoming()->public();
        
        if ($userId) {
            $user = User::find($userId);
            if ($user && $user->user_type === 'student') {
                $query = self::forStudent($userId)->upcoming();
            }
        }
        
        return $query->limit($limit)->get();
    }

    /**
     * Check if user can view this event
     */
    public function canView($userId = null)
    {
        // Public events can be viewed by anyone
        if ($this->is_public) {
            return true;
        }
        
        if (!$userId) {
            return false;
        }
        
        $user = User::find($userId);
        
        if (!$user) {
            return false;
        }
        
        // Creator can view their own events
        if ($this->created_by == $userId) {
            return true;
        }
        
        // Admins can view all events
        if (in_array($user->user_type, ['admin', 'super_admin'])) {
            return true;
        }
        
        // Check if event is for specific class/section that user belongs to
        if ($user->user_type === 'student' && $user->studentRecord) {
            $classId = $user->studentRecord->my_class_id;
            $sectionId = $user->studentRecord->section_id;
            
            if ($this->class_id && $this->section_id) {
                return $this->class_id == $classId && $this->section_id == $sectionId;
            } elseif ($this->class_id) {
                return $this->class_id == $classId;
            }
        }
        
        return false;
    }
}