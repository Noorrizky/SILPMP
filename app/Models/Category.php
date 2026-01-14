<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    
    protected $guarded = []; // Izinkan semua kolom diisi (Mass Assignment)

    public function parameters()
    {
        return $this->hasMany(Parameter::class);
    }
}