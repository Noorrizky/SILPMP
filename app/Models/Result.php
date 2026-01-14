<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function parameter()
    {
        return $this->belongsTo(Parameter::class);
    }
}