<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'library_code',
        'library_name',
        'reference_number',
        'call_number',
        'catalog_link',
        'main_link',     // NEW: Direct link to book page
        'alt_link',      // NEW: Alternative/similar book link
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'book_id' => 'integer',
        ];
    }

    // Relationships

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // Scopes

    public function scopeByLibraryCode($query, $code)
    {
        return $query->where('library_code', $code);
    }

    public function scopeUH($query)
    {
        return $query->where('library_code', 'UH');
    }

    public function scopeCOM($query)
    {
        return $query->where('library_code', 'COM');
    }

    public function scopeCOMFSM($query)
    {
        return $query->where('library_code', 'COM-FSM');
    }

    public function scopeMARC($query)
    {
        return $query->where('library_code', 'MARC');
    }

    public function scopeMICSEM($query)
    {
        return $query->where('library_code', 'MICSEM');
    }

    public function scopeLIB5($query)
    {
        return $query->where('library_code', 'LIB5');
    }

    // Helper Methods

    public function hasPhysicalCopy()
    {
        return !empty($this->reference_number) || !empty($this->call_number);
    }

    public function hasMainLink()
    {
        return !empty($this->main_link);
    }

    public function hasAltLink()
    {
        return !empty($this->alt_link);
    }

    /**
     * Library code constants
     */
    public const LIBRARY_UH = 'UH';
    public const LIBRARY_COM = 'COM';
    public const LIBRARY_COM_FSM = 'COM-FSM';
    public const LIBRARY_MARC = 'MARC';
    public const LIBRARY_MICSEM = 'MICSEM';
    public const LIBRARY_LIB5 = 'LIB5';

    /**
     * Get all available library codes with names
     */
    public static function getLibraryCodes(): array
    {
        return [
            self::LIBRARY_UH => 'University of Hawaii',
            self::LIBRARY_COM => 'College of Micronesia',
            self::LIBRARY_COM_FSM => 'College of Micronesia - FSM',
            self::LIBRARY_MARC => 'University of Guam (MARC)',
            self::LIBRARY_MICSEM => 'Micronesian Seminar',
            self::LIBRARY_LIB5 => 'Library #5 (Reserved)',
        ];
    }
}
