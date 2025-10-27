<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relationships

    public function bookmarks()
    {
        return $this->hasMany(BookBookmark::class);
    }

    public function ratings()
    {
        return $this->hasMany(BookRating::class);
    }

    public function reviews()
    {
        return $this->hasMany(BookReview::class);
    }

    public function views()
    {
        return $this->hasMany(BookView::class);
    }

    public function downloads()
    {
        return $this->hasMany(BookDownload::class);
    }

    public function termsAcceptances()
    {
        return $this->hasMany(UserTermsAcceptance::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeRegularUsers($query)
    {
        return $query->where('role', 'user');
    }

    // Helper Methods

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin() && $this->is_active;
    }

    public function hasAcceptedTerms($versionId = null)
    {
        $query = $this->termsAcceptances();

        if ($versionId) {
            $query->where('terms_version_id', $versionId);
        }

        return $query->exists();
    }
}
