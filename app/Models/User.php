<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'staff_id', 'name', 'email', 'password', 'role', 'auth_provider', 'provider_id',
        'nip', 'unit_name', 'avatar', 'active', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token', 'provider_id'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAuthor(): bool
    {
        return $this->role === 'author';
    }

    public function usesSso(): bool
    {
        return in_array($this->auth_provider, ['kemenag_sso', 'local_kemenag_sso'], true);
    }

    public function usesSsoOnly(): bool
    {
        return $this->auth_provider === 'kemenag_sso';
    }

    public function allowsManualLogin(): bool
    {
        return in_array($this->auth_provider, ['local', 'local_kemenag_sso'], true);
    }
}
