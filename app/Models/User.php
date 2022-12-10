<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    public function userAddress()
    {
        return $this->hasOne(UserAddress::class);
    }

    public function userActivity()
    {
        return $this->hasOne(UserActivity::class);
    }

    public function userRoles()
    {
        return $this->hasMany(UserRole::class);
    }
}
