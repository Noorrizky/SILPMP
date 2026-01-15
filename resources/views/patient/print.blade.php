<!DOCTYPE html>
<html>
<head>
    <title>Hasil Lab - {{ $registration->registration_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 11px; }
        
        .meta-table { width: 100%; margin-bottom: 20px; }
        .meta-table td { padding: 3px; vertical-align: top; }
        .label { font-weight: bold; width: 120px; }

        .result-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .result-table th, .result-table td { border: 1px solid #999; padding: 6px; text-align: left; }
        .result-table th { background-color: #f0f0f0; text-transform: uppercase; font-size: 10px; }

        .footer { width: 100%; margin-top: 50px; }
        .signature { width: 200px; text-align: center; float: right; }
        .signature-line { margin-top: 60px; border-bottom: 1px solid #000; }
        
        .badge-done { color: green; border: 1px solid green; padding: 2px 5px; font-size: 9px; border-radius: 3px; }
        .abnormal { 
            color: red; 
            font-weight: bold; 
        }
            </style>
</head>
<body>

    <div class="header">
        <h1>Puskesmas Murung Pudak</h1>
        <p> Jl. Sutomo Jl. Garuda Pangkalan, Belimbing, Kec. Murung Pudak, Kabupaten Tabalong, Kalimantan Selatan 71571</p>
        <p>Telp: (0511) 477-XXXX | Email: lab@tes.com</p>
    </div>

    <table class="meta-table">
        <tr>
            <td class="label">No. Registrasi</td>
            <td>: {{ $registration->registration_number }}</td>
            <td class="label">Tgl. Periksa</td>
            <td>: {{ $registration->created_at->format('d/m/Y H:i') }} WITA</td>
        </tr>
        <tr>
            <td class="label">Nama Pasien</td>
            <td>: <b>{{ $registration->patient->name }}</b></td>
            <td class="label">Dokter Pengirim</td>
            <td>: {{ $registration->doctor_sender ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">NIK / Usia</td>
            <td>: {{ $registration->patient->nik }} / {{ \Carbon\Carbon::parse($registration->patient->dob)->age }} Thn</td>
            <td class="label">Status</td>
            <td>: <span class="badge-done">VALIDATED</span></td>
        </tr>
        <tr>
            <td class="label">Alamat</td>
            <td colspan="3">: {{ $registration->patient->address ?? '-' }}</td>
        </tr>
    </table>

    <h3 style="margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px;">HASIL PEMERIKSAAN</h3>
    
    <table class="result-table">
        <thead>
            <tr>
                <th>Pemeriksaan</th>
                <th>Hasil</th>
                <th>Satuan</th>
                <th>Nilai Rujukan</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
        @foreach($registration->results as $res)
        @php
            // 1. Tentukan Nilai Normal berdasarkan Gender Pasien
            $range = ($registration->patient->gender == 'L') 
                ? $res->parameter->ref_range_male 
                : $res->parameter->ref_range_female;

            $is_abnormal = false;

            // 2. Logika Cek Angka (Hanya jalan jika Range formatnya "Angka-Angka", misal "12-16")
            if (str_contains($range, '-')) {
                $bounds = explode('-', $range);
                if (count($bounds) == 2 && is_numeric($res->result_value)) {
                    $min = (float) trim($bounds[0]);
                    $max = (float) trim($bounds[1]);
                    $val = (float) $res->result_value;

                    // Cek apakah di luar batas
                    if ($val < $min || $val > $max) {
                        $is_abnormal = true;
                    }
                }
            }
        @endphp

        <tr>
            <td>{{ $res->parameter->name }}</td>
            
            <td class="{{ $is_abnormal ? 'abnormal' : '' }}">
                {{ $res->result_value }}
                
                @if($is_abnormal)
                    <span style="font-size: 10px;">({{ $val > $max ? 'HIGH' : 'LOW' }})</span>
                @endif
            </td>
            
            <td>{{ $res->parameter->unit }}</td>
            <td>{{ $range }}</td> <td>{{ $res->note ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
    </table>

    <div style="font-size: 10px; font-style: italic; margin-bottom: 20px;">
        * Nilai rujukan disesuaikan dengan jenis kelamin dan usia pasien.
    </div>

    <div class="footer">
        <div class="signature">
            <p>Tabalong, {{ date('d F Y') }}</p>
            <p>Pemeriksa,</p>
            
            <div class="signature-line"></div>
            
            <p><b>{{ $registration->user->name }}</b></p>
            <p>Petugas</p>
        </div>
    </div>

</body>
</html>