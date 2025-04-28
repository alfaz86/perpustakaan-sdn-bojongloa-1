<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'admin';
    const ROLE_OFFICER = 'officer';
    const ROLES = [
        self::ROLE_USER,
        self::ROLE_ADMIN,
        self::ROLE_OFFICER,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === self::ROLE_ADMIN || $this->role === self::ROLE_OFFICER;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ?? 'https://ui-avatars.com/api/?name=' . $this->getInitials() . '&color=FFFFFF&background=09090b';
    }

    private function getInitials(): string
    {
        $words = explode(' ', $this->name);

        if (count($words) === 1) {
            return strtoupper(substr($words[0], 0, 1));
        }

        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    }
}
