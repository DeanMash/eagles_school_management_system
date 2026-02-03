<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BookTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'student_id',
        'issued_by',
        'issue_date',
        'due_date',
        'return_date',
        'status',
        'fine_amount',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'datetime',
        'due_date' => 'datetime',
        'return_date' => 'datetime',
        'fine_amount' => 'decimal:2',
    ];

    /**
     * Get the book for this transaction
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the student who borrowed the book
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the librarian who issued the book
     */
    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Calculate fine for overdue books
     */
    public function calculateFine()
    {
        if ($this->status !== 'issued' || !$this->due_date) {
            return 0;
        }

        $now = Carbon::now();
        if ($now->lte($this->due_date)) {
            return 0;
        }

        $daysOverdue = $now->diffInDays($this->due_date);
        $finePerDay = 10; // $10 per day overdue
        
        return $daysOverdue * $finePerDay;
    }

    /**
     * Check if transaction is overdue
     */
    public function isOverdue()
    {
        return $this->status === 'issued' && 
               $this->due_date && 
               Carbon::now()->gt($this->due_date);
    }

    /**
     * Get days overdue
     */
    public function getDaysOverdueAttribute()
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        return Carbon::now()->diffInDays($this->due_date);
    }
}
