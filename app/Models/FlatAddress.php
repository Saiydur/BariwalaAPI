<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlatAddress extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }
}
