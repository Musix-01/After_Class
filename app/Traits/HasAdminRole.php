<?php

namespace App\Traits;

trait HasAdminRole
{
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}