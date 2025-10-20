<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publisher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'program_name',
        'address',
        'website',
        'contact_email',
        'established_year',
    ];

    protected function casts(): array
    {
        return [
            'established_year' => 'integer',
        ];
    }

    // Relationships

    public function books()
    {
        return $this->hasMany(Book::class);
    }

    // Scopes

    public function scopeWithProgram($query)
    {
        return $query->whereNotNull('program_name');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%{$term}%")
            ->orWhere('program_name', 'like', "%{$term}%");
    }
}
