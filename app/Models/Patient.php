<?php

namespace App\Models;

// 1. HAPUS/GANTI baris 'use Illuminate\Database\Eloquent\Model;' menjadi ini:
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Database\Eloquent\Factories\HasFactory;

// 2. Ubah 'extends Model' menjadi 'extends Authenticatable'
class Patient extends Authenticatable
{
    use HasFactory;

    protected $guarded = [];

    // Relasi ke registrasi (history)
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
}