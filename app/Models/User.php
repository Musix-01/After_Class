<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasAdminRole;

class User extends Authenticatable
{
        public function isSuspended(): bool
    {
        if ($this->suspended_at === null) {
            return false;
        }

        if (
            $this->suspended_until !== null &&
            now()->greaterThanOrEqualTo($this->suspended_until)
        ) {
            return false;
        }

        return true;
    }

    public function scopeSuspended($query)
    {
        return $query->whereNotNull('suspended_at')
            ->where(function ($q) {
                $q->whereNull('suspended_until')
                ->orWhere('suspended_until', '>', now());
            });
    }
    use HasFactory, Notifiable, HasAdminRole;

    protected $fillable = [
        'name',
        'username',
        'birthday',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'suspended_at' => 'datetime',
            'suspended_until' => 'datetime',
            'admin_seen_at' => 'datetime',
        ];
    }
}