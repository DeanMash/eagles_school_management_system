<?php

namespace App\Models;

use Eloquent;
use Carbon\Carbon;

class TimeTableRecord extends Eloquent
{
    protected $fillable = ['name', 'my_class_id', 'exam_id', 'year', 'start_date', 'end_date'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function my_class()
    {
        return $this->belongsTo(MyClass::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function timetable_slots()
    {
        return $this->hasMany(TimetableSlot::class, 'ttr_id');
    }

    /**
     * Get week dates (Mon–Sun) from start_date or current week.
     */
    public function getWeekDates($startDate = null)
    {
        $start = $startDate ? Carbon::parse($startDate) : ($this->start_date ? Carbon::parse($this->start_date) : Carbon::now()->startOfWeek(Carbon::MONDAY));
        $start = $start->copy()->startOfWeek(Carbon::MONDAY);
        $dates = [];
        for ($i = 0; $i < 7; $i++) {
            $d = $start->copy()->addDays($i);
            $dates[$d->format('Y-m-d')] = [
                'date' => $d->format('Y-m-d'),
                'day_name' => $d->format('l'),
                'display' => $d->format('D, M j'),
                'is_today' => $d->isToday(),
            ];
        }
        return $dates;
    }

    /**
     * Get time slots 7:30–15:30 in 30-min steps (16 slots).
     */
    public static function getTimeSlotsArray()
    {
        $slots = [];
        $start = Carbon::createFromTime(7, 30, 0);
        for ($i = 0; $i < 16; $i++) {
            $slotStart = $start->copy()->addMinutes(30 * $i);
            $slotEnd = $slotStart->copy()->addMinutes(30);
            $key = $slotStart->format('H:i');
            $slots[$key] = [
                'start' => $slotStart->format('H:i'),
                'end' => $slotEnd->format('H:i'),
                'display' => $slotStart->format('h:i A') . ' - ' . $slotEnd->format('h:i A'),
            ];
        }
        return $slots;
    }
}
