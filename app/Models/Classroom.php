<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $fillable = ['room_number', 'capacity', 'building'];

    public function getDisplayNameAttribute()
    {
        return $this->building ? "{$this->room_number} ({$this->building})" : $this->room_number;
    }
}
