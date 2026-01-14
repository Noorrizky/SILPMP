<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientAuthController extends Controller
{
    // Tampilkan Form Login
    public function showLoginForm()
    {
        return view('patient.login');
    }

    // Proses Login
    public function login(Request $request)
    {
        $request->validate([
            'nik' => 'required|numeric',
            'dob' => 'required|date',
        ]);

        // 1. Cari Pasien by NIK
        $patient = Patient::where('nik', $request->nik)->first();

        // 2. Cek apakah Tanggal Lahir cocok
        if ($patient && $patient->dob == $request->dob) {
            // 3. Login menggunakan Guard 'patient'
            Auth::guard('patient')->login($patient);
            
            // Regenerate session biar aman
            $request->session()->regenerate();

            return redirect()->route('patient.dashboard');
        }

        // Kalau gagal
        return back()->withErrors([
            'nik' => 'NIK atau Tanggal Lahir tidak ditemukan.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('patient')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}