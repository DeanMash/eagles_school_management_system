<?php

namespace App\Models;

use Eloquent;

class TimetableSlot extends Eloquent
{
    protected $fillable = [
        'ttr_id', 'date', 'start_time', 'end_time',
        'subject_id', 'teacher_id', 'classroom_id', 'notes'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function tt_record()
    {
        return $this->belongsTo(TimeTableRecord::class, 'ttr_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(\App\Models\User::class, 'teacher_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}
