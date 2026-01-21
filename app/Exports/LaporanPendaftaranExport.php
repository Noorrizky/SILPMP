<?php

namespace App\Exports;

use App\Models\Registration;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings; // 1. Import WithDrawings
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing; // 2. Import Class Drawing
use Carbon\Carbon;

// 3. Tambahkan "WithDrawings" di implements
class LaporanPendaftaranExport implements FromView, ShouldAutoSize, WithDrawings
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        // ... (Kode query view sama seperti sebelumnya, tidak berubah) ...
        
        $query = Registration::query()->with(['patient', 'results.parameter']);
        $data = $this->filters;

        if (isset($data['status']['value']) && $data['status']['value']) {
            $query->where('status', $data['status']['value']);
        }

        if (isset($data['created_at'])) {
            $rangeData = $data['created_at'];
            if (isset($rangeData['range'])) {
                match ($rangeData['range']) {
                    'today' => $query->whereDate('created_at', now()),
                    '7_days' => $query->where('created_at', '>=', now()->subDays(7)),
                    '30_days' => $query->where('created_at', '>=', now()->subDays(30)),
                    'this_month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                    'this_year' => $query->whereYear('created_at', now()->year),
                    'last_year' => $query->where('created_at', '>=', now()->subYear()),
                    'custom' => $query
                        ->when($rangeData['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($rangeData['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date)),
                    default => $query,
                };
            }
        }

        return view('exports.laporan_pendaftaran', [
            'registrations' => $query->latest()->get()
        ]);
    }

    // 4. Method baru untuk menampilkan Logo
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo Kabupaten');
        $drawing->setDescription('Logo Kabupaten Tabalong');
        
        // Pastikan path gambarnya benar sesuai nama file Anda di folder public
        $drawing->setPath(public_path('logo_tabalong.png')); 
        
        $drawing->setHeight(90); // Tinggi gambar (sesuaikan agar pas dengan header teks)
        $drawing->setCoordinates('A1'); // Posisi di pojok kiri atas
        
        // Geser sedikit agar tidak terlalu mepet kiri & atas
        $drawing->setOffsetX(10); 
        $drawing->setOffsetY(10); 

        return [$drawing];
    }
}