<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'location_id',
    ];

    protected function casts(): array
    {
        return [
            'book_id' => 'integer',
            'location_id' => 'integer',
        ];
    }

    // Relationships

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function geographicLocation()
    {
        return $this->belongsTo(GeographicLocation::class, 'location_id');
    }
}
