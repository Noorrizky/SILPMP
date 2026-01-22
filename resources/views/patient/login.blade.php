<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pasien - Puskesmas Murung Pudak</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
        
        <div class="bg-blue-600 p-8 text-center">
            <div class="mx-auto w-16 h-16 rounded-full flex items-center justify-center mb-4 backdrop-blur-sm">
                <img 
                    src="{{ asset('logo_tabalong.png') }}" 
                    alt="Logo Tabalong" 
                    class="w-30 h-30 object-contain"
                >
            </div>
            <h2 class="text-2xl font-bold text-white">SMART LAB</h2>
            <h3 class="text-blue-100 font-bold mt-3">Sistem Digitalisasi Pencatatan dan Pelaporan Hasil Laboratorium</h3>
            <p class="text-blue-100 text-sm mt-3">Cek riwayat kesehatan Anda dengan mudah</p>
        </div>

        <div class="p-8">
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded text-sm">
                    <p class="font-bold">Gagal Masuk</p>
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                
                <div class="mb-5">
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Nomor Induk Kependudukan (NIK)</label>
                    <input 
                        type="number" 
                        name="nik" 
                        inputmode="numeric" 
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" 
                        placeholder="Contoh: 637201xxxxxx" 
                        required
                    >
                    </div>

                <div class="mb-8">
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Tanggal Lahir</label>
                    <input 
                        type="date" 
                        name="dob" 
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 cursor-pointer" 
                        required
                    >
                    <p class="text-xs text-gray-400 mt-1">*Pastikan sesuai dengan KTP</p>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-lg shadow-lg hover:shadow-xl transition duration-200 transform hover:-translate-y-0.5">
                    Lihat Hasil Saya
                </button>
            </form>
        </div>
        
        <div class="bg-gray-50 p-4 text-center border-t border-gray-100">
            <p class="text-xs text-gray-400">&copy; {{ date('Y') }} SILPMP</p>
        </div>
    </div>

</body>
</html>