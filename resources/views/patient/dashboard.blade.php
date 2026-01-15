<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pasien - Puskesmas Murung Pudak</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen pb-10">

    <nav class="bg-white shadow-sm sticky top-0 z-10">
        <div class="max-w-3xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="bg-blue-600 text-white p-1.5 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>
                <span class="font-bold text-gray-800 tracking-tight">Puskesmas Murung Pudak</span>
            </div>
            
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="text-sm font-medium text-red-600 hover:text-red-800 flex items-center gap-1">
                    Keluar
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 mt-6">
        
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Halo, {{ Str::words($patient->name, 2) }}!</h1>
            <p class="text-gray-500 text-sm mt-1">Berikut adalah riwayat pemeriksaan laboratorium Anda.</p>
        </div>

        @if($history->isEmpty())
            <div class="text-center py-12 bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="bg-blue-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Belum Ada Hasil</h3>
                <p class="text-gray-500 text-sm max-w-xs mx-auto mt-2">Hasil pemeriksaan Anda akan muncul di sini setelah divalidasi oleh dokter.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($history as $reg)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden transition hover:shadow-md">
                    
                    <div class="bg-gray-50 px-5 py-3 border-b border-gray-100 flex justify-between items-center">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal Periksa</p>
                            <p class="text-sm font-bold text-gray-800">{{ $reg->created_at->format('d M Y') }} <span class="text-gray-400 font-normal text-xs">• {{ $reg->created_at->format('H:i') }}</span></p>
                        </div>
                        <div class="text-right">
                             <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Selesai
                            </span>
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="flex justify-between items-end mb-4">
                            <div>
                                <p class="text-xs text-gray-400 mb-1">No. Registrasi</p>
                                <p class="font-mono text-sm text-gray-600">{{ $reg->registration_number }}</p>
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-lg p-3 mb-5">
                            <p class="text-xs font-bold text-gray-400 mb-2 uppercase">Preview Hasil</p>
                            <div class="space-y-2">
                                @foreach($reg->results as $res)
                                <div class="flex justify-between text-sm border-b border-gray-200 last:border-0 pb-1 last:pb-0">
                                    <span class="text-gray-600 truncate pr-2">{{ $res->parameter->name }}</span>
                                    <span class="font-bold text-gray-800 whitespace-nowrap">{{ $res->result_value }} <span class="text-xs font-normal text-gray-500">{{ $res->parameter->unit }}</span></span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <a href="{{ route('patient.print', $reg->id) }}" target="_blank" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition shadow-lg shadow-blue-200 active:scale-95 flex justify-center items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download PDF Resmi
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
        
        <div class="text-center mt-8 pb-8">
            <p class="text-xs text-gray-400">Jika terdapat kesalahan data, silakan hubungi petugas lab.</p>
        </div>
    </div>

</body>
</html>