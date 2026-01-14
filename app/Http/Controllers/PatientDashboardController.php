<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Registration;
use Barryvdh\DomPDF\Facade\Pdf;

class PatientDashboardController extends Controller
{
    public function index()
    {
        $patient = Auth::guard('patient')->user();

        // Ambil history pemeriksaan yang sudah SELESAI (done)
        $history = Registration::with(['results.parameter']) // Eager load
                    ->where('patient_id', $patient->id)
                    ->where('status', 'done') 
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('patient.dashboard', compact('patient', 'history'));
    }

    public function print($id)
    {
        $patient = Auth::guard('patient')->user();

        // Pastikan pasien cuma bisa print punya dia sendiri
        $registration = Registration::with(['results.parameter', 'patient', 'user'])
                        ->where('id', $id)
                        ->where('patient_id', $patient->id)
                        ->where('status', 'done')
                        ->firstOrFail();

        // Generate PDF
        $pdf = Pdf::loadView('patient.print', compact('registration'));
        return $pdf->stream('Hasil-Lab-' . $registration->registration_number . '.pdf');
    }
}