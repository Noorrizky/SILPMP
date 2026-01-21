<table>
    <thead>
        {{-- KOP SURAT --}}
        <tr>
            <td colspan="6" style="text-align: center; font-size: 12pt; font-weight: bold;">PEMERINTAH KABUPATEN TABALONG</td>
        </tr>
        <tr>
            <td colspan="6" style="text-align: center; font-size: 14pt; font-weight: bold;">DINAS KESEHATAN</td>
        </tr>
        <tr>
            <td colspan="6" style="text-align: center; font-size: 16pt; font-weight: bold;">UPT PUSKESMAS MURUNG PUDAK</td>
        </tr>
        <tr>
            <td colspan="6" style="text-align: center; font-size: 10pt; font-style: italic;">
                Jln. Pangkalan Rahayu RT. 11, Murung Pudak, Tabalong, Kalimantan Selatan 71571
            </td>
        </tr>
        <tr>
            <td colspan="6" style="text-align: center; font-size: 10pt;">
                Telp: 08115000487 | Email: murungpudakpkm@gmail.com
            </td>
        </tr>
        <tr>
            <td colspan="6" style="text-align: center; font-size: 10pt;">
                Laman : pkm-murungpudak.tabalongkab.go.id
            </td>
        </tr>
        
        {{-- JARAK KOSONG --}}
        <tr><td colspan="6"></td></tr>

        {{-- JUDUL TABEL --}}
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #4CAF50; color: #000000; text-align: center;">No. Registrasi</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #4CAF50; color: #000000; text-align: center;">NIK Pasien</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #4CAF50; color: #000000; text-align: center;">Nama Pasien</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #4CAF50; color: #000000; text-align: center;">Jenis Pemeriksaan</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #4CAF50; color: #000000; text-align: center;">Waktu Daftar</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #4CAF50; color: #000000; text-align: center;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($registrations as $reg)
            <tr>
                <td style="border: 1px solid #000000;">{{ $reg->registration_number }}</td>
                <td style="border: 1px solid #000000;">{{ $reg->patient->nik ?? '-' }}</td>
                <td style="border: 1px solid #000000;">{{ $reg->patient->name ?? '-' }}</td>
                <td style="border: 1px solid #000000;">
                    {{ $reg->results->map(fn($r) => $r->parameter->name)->join(', ') }}
                </td>
                <td style="border: 1px solid #000000;">
                    {{ \Carbon\Carbon::parse($reg->created_at)->translatedFormat('d F Y H:i') }}
                </td>
                <td style="border: 1px solid #000000;">
                    @switch($reg->status)
                        @case('pending') Menunggu Sampel @break
                        @case('processing') Sedang Diperiksa @break
                        @case('done') Selesai @break
                        @default {{ $reg->status }}
                    @endswitch
                </td>
            </tr>
        @endforeach
    </tbody>
</table>