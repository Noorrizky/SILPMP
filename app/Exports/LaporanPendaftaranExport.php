<?php

namespace App\Exports;

use App\Models\Registration;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Carbon\Carbon;

class LaporanPendaftaranExport implements FromView, ShouldAutoSize, WithDrawings
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $query = Registration::query()->with(['patient', 'results.parameter']);
        $data = $this->filters;

        // 1. Filter Status
        if (isset($data['status']['value']) && $data['status']['value']) {
            $query->where('status', $data['status']['value']);
        }

        // 2. Filter Tanggal & Pembuatan Label Periode
        $filterLabel = 'Semua Waktu'; // Default label

        if (isset($data['created_at'])) {
            $rangeData = $data['created_at'];
            
            if (isset($rangeData['range'])) {
                $range = $rangeData['range'];

                // --- LOGIKA FILTER QUERY ---
                match ($range) {
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

                // --- LOGIKA PEMBUATAN LABEL (Sama seperti indicateUsing) ---
                if ($range === 'custom') {
                    $txt = 'Custom';
                    if (!empty($rangeData['from'])) {
                        $txt .= ' dari ' . Carbon::parse($rangeData['from'])->format('d M Y');
                    }
                    if (!empty($rangeData['until'])) {
                        $txt .= ' s/d ' . Carbon::parse($rangeData['until'])->format('d M Y');
                    }
                    $filterLabel = $txt;
                } else {
                    $labels = [
                        'today' => 'Hari Ini (' . date('d M Y') . ')',
                        '7_days' => '7 Hari Terakhir',
                        '30_days' => '30 Hari Terakhir',
                        'this_month' => 'Bulan Ini (' . date('F Y') . ')',
                        'this_year' => 'Tahun Ini (' . date('Y') . ')',
                        'last_year' => '1 Tahun Terakhir',
                    ];
                    $filterLabel = $labels[$range] ?? $range;
                }
            }
        }

        $registrations = $query->latest()->get();

        return view('exports.laporan_pendaftaran', [
            'registrations' => $registrations,
            'filterLabel' => $filterLabel, // Kirim label ke view
            'totalData' => $registrations->count() // Kirim total data
        ]);
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo Kabupaten');
        $drawing->setDescription('Logo Kabupaten Tabalong');
        $drawing->setPath(public_path('logo_tabalong.png')); 
        $drawing->setHeight(90); 
        $drawing->setCoordinates('A1'); 
        $drawing->setOffsetX(10); 
        $drawing->setOffsetY(10); 

        return [$drawing];
    }
}