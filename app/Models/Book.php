<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'title',
        'author',
        'isbn',
        'publisher',
        'rack_no',
        'quantity',
        'available_qty',
    ];

    public function bookIssues(): HasMany
    {
        return $this->hasMany(BookIssue::class, 'book_id');
    }
}
