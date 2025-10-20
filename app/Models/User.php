<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'bio',
        'avatar',
        'phone',
        'department',
        'is_cms_user',
        'cms_preferences',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'is_cms_user' => 'boolean',
            'is_active' => 'boolean',
            'last_cms_access' => 'datetime',
            'cms_preferences' => 'array',
        ];
    }

    /**
     * Get user's full name
     */
    public function getFullNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return "{$this->first_name} {$this->last_name}";
        }

        return $this->name;
    }

    /**
     * Get user's initials
     */
    public function getInitialsAttribute(): string
    {
        $name = $this->full_name;
        $nameParts = explode(' ', $name);

        if (count($nameParts) >= 2) {
            return strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }

    /**
     * Get user's avatar URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }

        // Generate avatar using initials
        return "https://ui-avatars.com/api/?name={$this->initials}&size=100&background=0D47A1&color=fff";
    }

}
