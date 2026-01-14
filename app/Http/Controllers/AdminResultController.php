<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AdminResultController extends Controller
{
    public function print(Registration $record)
    {
        // 1. Load data relasi (sama seperti di pasien)
        $record->load(['results.parameter', 'patient', 'user']);

        // 2. Gunakan View yang SAMA dengan milik pasien (Reuse View)
        // Kita tidak perlu bikin view baru, pakai 'patient.print' saja
        $pdf = Pdf::loadView('patient.print', [
            'registration' => $record
        ]);

        // 3. Stream PDF (Buka di browser)
        return $pdf->stream('Admin-Print-' . $record->registration_number . '.pdf');
    }
}