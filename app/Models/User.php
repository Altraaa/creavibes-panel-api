<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'profile_photo_path',
        'phone_number',
        'bio',
        'role',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the authentications for the user.
     */
    public function authentications(): HasMany
    {
        return $this->hasMany(Authentication::class);
    }

    /**
     * Check if the user is active.
     */
    public function isActive(): bool
    {
        return $this->is_active ?? true;
    }

    /**
     * Check if the user has verified email.
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Mark the user's email as verified.
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => now(),
        ])->save();
    }

    /**
     * Get the user's role.
     */
    public function getRole(): string
    {
        return $this->role ?? 'user';
    }

    /**
     * Check if user has admin role.
     */
    public function isAdmin(): bool
    {
        return $this->getRole() === 'admin';
    }

    /**
     * Check if user has specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->getRole() === $role;
    }

    /**
     * Update last login information.
     */
    public function updateLastLogin(string $ipAddress): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ]);
    }
}
