<table>
    <thead>
        {{-- KOP SURAT (Colspan 8) --}}
        <tr>
            <td colspan="8" style="text-align: center; font-size: 12pt; font-weight: bold;">PEMERINTAH KABUPATEN TABALONG</td>
        </tr>
        <tr>
            <td colspan="8" style="text-align: center; font-size: 14pt; font-weight: bold;">DINAS KESEHATAN</td>
        </tr>
        <tr>
            <td colspan="8" style="text-align: center; font-size: 16pt; font-weight: bold;">UPT PUSKESMAS MURUNG PUDAK</td>
        </tr>
        <tr>
            <td colspan="8" style="text-align: center; font-size: 10pt; font-style: italic;">
                Jln. Pangkalan Rahayu RT. 11, Murung Pudak, Tabalong, Kalimantan Selatan 71571
            </td>
        </tr>
        <tr>
            <td colspan="8" style="text-align: center; font-size: 10pt;">
                Telp: 08115000487 | Email: murungpudakpkm@gmail.com
            </td>
        </tr>
        <tr>
            <td colspan="8" style="text-align: center; font-size: 10pt;">
                Laman : pkm-murungpudak.tabalongkab.go.id
            </td>
        </tr>
        
        <tr><td colspan="8"></td></tr>

        {{-- INFO FILTER --}}
        <tr>
            <td colspan="8" style="font-weight: bold;">Periode Laporan : {{ $filterLabel }}</td>
        </tr>
        <tr>
            <td colspan="8" style="font-weight: bold;">Total Data : {{ $totalData }} Pasien</td>
        </tr>
        
        <tr><td colspan="8"></td></tr>

        {{-- JUDUL TABEL --}}
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #4CAF50; color: #000000; text-align: center; width: 30px;">No</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #4CAF50; color: #000000; text-align: center;">No. Registrasi</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #4CAF50; color: #000000; text-align: center;">NIK Pasien</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #4CAF50; color: #000000; text-align: center;">Nama Pasien</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #4CAF50; color: #000000; text-align: center;">Jenis Pemeriksaan</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #4CAF50; color: #000000; text-align: center;">Hasil Pemeriksaan</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #4CAF50; color: #000000; text-align: center;">Waktu Daftar</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #4CAF50; color: #000000; text-align: center;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($registrations as $reg)
            <tr>
                {{-- Gunakan valign="top" agar teks rata atas semua --}}
                <td valign="top" style="border: 1px solid #000000; text-align: center;">{{ $loop->iteration }}</td>
                <td valign="top" style="border: 1px solid #000000;">{{ $reg->registration_number }}</td>
                <td valign="top" style="border: 1px solid #000000;">&nbsp;{{ $reg->patient->nik ?? '-' }}</td>
                <td valign="top" style="border: 1px solid #000000;">{{ $reg->patient->name ?? '-' }}</td>
                
                {{-- JENIS PEMERIKSAAN (Looping + BR) --}}
                <td valign="top" style="border: 1px solid #000000;">
                    @foreach($reg->results as $result)
                        {{-- Tampilkan nama pemeriksaan --}}
                        - {{ $result->parameter->name }}
                        
                        {{-- Tambahkan Enter (<br>) jika bukan item terakhir --}}
                        @if(!$loop->last) <br> @endif
                    @endforeach
                </td>

                {{-- HASIL PEMERIKSAAN (Looping + BR) --}}
                <td valign="top" style="border: 1px solid #000000;">
                    @foreach($reg->results as $result)
                        {{-- Tampilkan Hasil + Satuan --}}
                        {{ $result->result_value }} {{ $result->parameter->unit ?? '' }}
                        
                        {{-- Tambahkan Enter (<br>) jika bukan item terakhir --}}
                        @if(!$loop->last) <br> @endif
                    @endforeach
                </td>

                <td valign="top" style="border: 1px solid #000000;">
                    {{ \Carbon\Carbon::parse($reg->created_at)->translatedFormat('d F Y H:i') }}
                </td>
                <td valign="top" style="border: 1px solid #000000;">
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