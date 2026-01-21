<!DOCTYPE html>
<html>
<head>
    <title>Hasil Lab - {{ $registration->registration_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #000; 
            padding-bottom: 10px; 
            position: relative; /* PENTING: Agar logo bisa diposisikan absolute terhadap header */
        }
        
        /* Style untuk Logo */
        .logo-image {
            position: absolute; /* Posisi bebas */
            top: 5px;           /* Jarak dari atas */
            left: 0px;          /* Jarak dari kiri */
            width: 75px;        /* Lebar logo, sesuaikan */
            height: auto;
        }

        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 11px; }
        
        /* Table Atas */
        .meta-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .meta-table td { padding: 3px; vertical-align: top; }
        .label { font-weight: bold; width: 130px; }

        /* Table Hasil */
        .result-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .result-table th, .result-table td { border: 1px solid #999; padding: 6px; text-align: left; }
        .result-table th { background-color: #f0f0f0; text-transform: uppercase; font-size: 10px; }

        .badge-done { color: green; border: 1px solid green; padding: 2px 5px; font-size: 9px; border-radius: 3px; }
        .abnormal { color: red; font-weight: bold; }

        /* PERBAIKAN FOOTER MENGGUNAKAN TABLE (BUKAN FLEX) */
        .footer-table { 
            width: 100%; 
            margin-top: 50px; 
            border: none; 
        }
        .footer-cell {
            width: 50%; /* Bagi 2 kolom rata */
            text-align: center;
            vertical-align: top;
        }
        .signature-space {
            height: 70px; /* Tinggi untuk tanda tangan */
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('logo_tabalong.png') }}" class="logo-image" alt="Logo Kab">
        <h1>PEMERINTAH KABUPATEN TABALONG</h1>
        <h1>DINAS KESEHATAN</h1>
        <h1>UPT Puskesmas Murung Pudak</h1>
        <p> Jln. Pangkalan Rahayu RT. 11, Murung Pudak, Tabalong, Kalimantan Selatan 71571</p>
        <p>Telp: 08115000487 | Email: murungpudakpkm@gmail.com</p>
        <p>Laman : pkm-murungpudak.tabalongkab.go.id</p>
    </div>

    <table class="meta-table">
        <tr>
            <td colspan="4" style="text-align: center; padding-bottom: 15px;">
                <h3 style="margin:0; text-decoration: underline;">HASIL PEMERIKSAAN LABORATORIUM</h3>
            </td>
        </tr>
        
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
            $range = ($registration->patient->gender == 'L') 
                ? $res->parameter->ref_range_male 
                : $res->parameter->ref_range_female;

            $is_abnormal = false;
            $val = (float) $res->result_value;
            $min = 0; $max = 0;

            if (str_contains($range, '-')) {
                $bounds = explode('-', $range);
                if (count($bounds) == 2 && is_numeric($res->result_value)) {
                    $min = (float) trim($bounds[0]);
                    $max = (float) trim($bounds[1]);
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
            <td>{{ $range }}</td> 
            <td>{{ $res->note ?? '-' }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>

    <div style="font-size: 10px; font-style: italic; margin-bottom: 20px;">
        * Nilai rujukan disesuaikan dengan jenis kelamin dan usia pasien.
    </div>

    <table class="footer-table">
        <tr>
            <td class="footer-cell">
                <p>&nbsp;</p> <p>Dokter Pengirim,</p>
                
                <div class="signature-space"></div>
                
                <p><b>{{ $registration->doctor_sender ?? '-' }}</b></p>
            </td>

            <td class="footer-cell">
                <p>Tabalong, {{ date('d F Y') }}</p>
                <p>Pemeriksa,</p>
                
                <div class="signature-space"></div>
                
                <p><b>{{ $registration->user->name }}</b></p>
                <p>Petugas Laboratorium</p>
            </td>
        </tr>
    </table>

</body>
</html>