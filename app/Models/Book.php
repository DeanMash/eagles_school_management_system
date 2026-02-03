<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'isbn',
        'name',
        'title',
        'author',
        'publisher',
        'year_published',
        'category',
        'copies',
        'available_copies',
        'shelf_number',
        'description',
        'book_cover',
        'status',
        'my_class_id',
    ];

    protected $casts = [
        'year_published' => 'integer',
        'copies' => 'integer',
        'available_copies' => 'integer',
    ];

    /**
     * Get all transactions for this book
     */
    public function transactions()
    {
        return $this->hasMany(BookTransaction::class);
    }

    /**
     * Get the class that owns this book (if applicable)
     */
    public function myClass()
    {
        return $this->belongsTo(MyClass::class, 'my_class_id');
    }

    /**
     * Check if book is available for issue
     */
    public function isAvailable()
    {
        return $this->status === 'available' && $this->available_copies > 0;
    }

    /**
     * Get book cover URL
     */
    public function getBookCoverUrlAttribute()
    {
        if ($this->book_cover) {
            return asset('storage/' . $this->book_cover);
        }
        return asset('images/default-book-cover.jpg');
    }
}
