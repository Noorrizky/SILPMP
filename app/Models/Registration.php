<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $guarded = [];

    public function patient() { return $this->belongsTo(Patient::class); }
    public function user() { return $this->belongsTo(User::class); }
    
    // Relasi ke hasil
    public function results() { return $this->hasMany(Result::class); }
}
