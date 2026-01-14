<?php

namespace App\Filament\Widgets;

use App\Models\Patient;
use App\Models\Registration;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    // Atur refresh otomatis tiap 15 detik (biar real-time)
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        return [
            // Stat 1: Total Pasien
            Stat::make('Total Pasien', Patient::count())
                ->description('Pasien terdaftar dalam database')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            // Stat 2: Menunggu Hasil (Pending/Processing)
            Stat::make('Dalam Proses', Registration::whereIn('status', ['pending', 'processing'])->count())
                ->description('Belum keluar hasil')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'), // Kuning

            // Stat 3: Selesai Hari Ini
            Stat::make('Selesai Hari Ini', Registration::where('status', 'done')->whereDate('created_at', today())->count())
                ->description('Hasil sudah valid')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'), // Hijau
        ];
    }
}