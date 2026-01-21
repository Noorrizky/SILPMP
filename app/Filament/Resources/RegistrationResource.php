<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationResource\Pages;
use App\Filament\Resources\RegistrationResource\RelationManagers;
use App\Models\Registration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;
use Filament\Forms\Get; // Jangan lupa import ini di paling atas file
use Carbon\Carbon; // Import Carbon untuk format tanggal
use App\Exports\LaporanPendaftaranExport;
use Maatwebsite\Excel\Facades\Excel;

class RegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Pendaftaran')
                    ->schema([
                        // Generate No. Reg Otomatis
                        Forms\Components\TextInput::make('registration_number')
                            ->default('LAB-' . date('Ymd') . '-' . rand(100, 999))
                            ->readOnly()
                            ->required(),
                        
                        Forms\Components\Select::make('patient_id')
                            ->relationship('patient', 'nik') // Biarkan 'nik' sebagai default sort
                            
                            // 1. Custom Tampilan (NIK - Nama)
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nik} - {$record->name}")
                            
                            // 2. Agar bisa dicari pakai NIK atau Nama
                            ->searchable(['nik', 'name']) 
                            
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nik')
                                    ->label('NIK')
                                    ->required()
                                    ->maxLength(16),
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required(),
                                Forms\Components\DatePicker::make('dob')
                                    ->label('Tanggal Lahir')
                                    ->required(),
                                Forms\Components\Select::make('gender')
                                    ->label('Jenis Kelamin')
                                    ->options([
                                        'L' => 'Laki-laki',
                                        'P' => 'Perempuan',
                                    ])
                                    ->required(),
                            ]),
                        Forms\Components\TextInput::make('doctor_sender')
                            ->label('Dokter Pengirim'),
                        
                        // Otomatis isi user_id dengan user yg login sekarang
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn () => auth()->id()),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Menunggu Sampel',
                                'processing' => 'Sedang Diperiksa',
                                'done' => 'Selesai (Valid)',
                            ])
                            ->default('pending')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Hasil Pemeriksaan')
                    ->schema([
                        Forms\Components\Repeater::make('results') // Relasi HasMany
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('parameter_id')
                                    ->label('Jenis Pemeriksaan')
                                    ->relationship('parameter', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive() // Supaya bisa mentrigger event saat dipilih
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Otomatis tarik snapshot nilai rujukan saat item dipilih
                                        $param = \App\Models\Parameter::find($state);
                                        if ($param) {
                                            $set('ref_range_snapshot', "P: {$param->ref_range_male} | W: {$param->ref_range_female}");
                                        }
                                    })
                                    ->columnSpan(1),
                                
                                Forms\Components\TextInput::make('result_value')
                                    ->label('Hasil')
                                    ->columnSpan(1),

                                // --- TAMBAHKAN INI ---
                                Forms\Components\TextInput::make('note')
                                    ->label('Keterangan')
                                    ->placeholder('Cth: Sampel Hemolisis')
                                    ->columnSpan(1),
                                // ---------------------

                                Forms\Components\TextInput::make('ref_range_snapshot')
                                    ->label('Rujukan')
                                    ->readOnly()
                                    ->columnSpan(1),
                            ])
                            ->columns(4) // Ubah jadi 4 kolom agar muat (Parameter, Hasil, Ket, Rujukan)
                            ->addActionLabel('Tambah Item Pemeriksaan'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                // Tables\Columns\TextColumn::make('registration_number')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('patient.nik')->label('Nik')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('patient.name')->label('Pasien')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('results.parameter.name')
                    ->label('Jenis Pemeriksaan')
                    ->badge() // Membuat tampilan seperti 'Tags' berwarna
                    ->separator(',') // Pemisah data
                    ->color('info') // Warna badge (bisa diganti primary, success, dll)
                    ->limitList(2) // Hanya tampilkan 2 item pertama, sisanya tertulis "+2 more"
                    ->searchable(), // Agar bisa dicari berdasarkan nama pemeriksaan
                Tables\Columns\TextColumn::make('created_at')->label('Tanggal')->sortable()->dateTime(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'processing',
                        'success' => 'done',
                    ]),
                
            ])
            ->filters([
                // 1. Filter Status (Yang sudah ada)
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Pemeriksaan')
                    ->options([
                        'pending' => 'Menunggu Sampel',
                        'processing' => 'Sedang Diperiksa',
                        'done' => 'Selesai (Valid)',
                    ]),

                // 2. Filter Tanggal (BARU)
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\Select::make('range')
                            ->label('Rentang Waktu')
                            ->options([
                                'today' => 'Hari Ini',
                                '7_days' => '7 Hari Terakhir',
                                '30_days' => '30 Hari Terakhir (1 Bulan)',
                                'this_month' => 'Bulan Ini',
                                'this_year' => 'Tahun Ini',
                                'last_year' => '1 Tahun Terakhir',
                                'custom' => 'Pilih Tanggal Sendiri (Custom)', // Opsi Baru
                            ])
                            ->default('30_days') // Opsional: set default
                            ->live(), // PENTING: Agar form bisa berubah dinamis saat dipilih

                        // Input Tanggal Mulai (Hanya muncul jika pilih 'custom')
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->visible(fn (Get $get) => $get('range') === 'custom'),

                        // Input Tanggal Sampai (Hanya muncul jika pilih 'custom')
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->visible(fn (Get $get) => $get('range') === 'custom'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['range'],
                                fn (Builder $query, $range) => match ($range) {
                                    'today' => $query->whereDate('created_at', now()),
                                    '7_days' => $query->where('created_at', '>=', now()->subDays(7)),
                                    '30_days' => $query->where('created_at', '>=', now()->subDays(30)),
                                    'this_month' => $query->whereMonth('created_at', now()->month)
                                                        ->whereYear('created_at', now()->year),
                                                        
                                    'this_year' => $query->whereYear('created_at', now()->year),
                                    'last_year' => $query->where('created_at', '>=', now()->subYear()),
                                    
                                    // Logika untuk Custom Date
                                    'custom' => $query
                                        ->when(
                                            $data['from'],
                                            fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date)
                                        )
                                        ->when(
                                            $data['until'],
                                            fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date)
                                        ),
                                        
                                    default => $query,
                                }
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if (! $data['range']) {
                            return $indicators;
                        }

                        // Jika user memilih Custom, tampilkan format tanggalnya
                        if ($data['range'] === 'custom') {
                            $indicator = 'Waktu: Custom';
                            if ($data['from']) {
                                $indicator .= ' dari ' . Carbon::parse($data['from'])->format('d M Y');
                            }
                            if ($data['until']) {
                                $indicator .= ' s/d ' . Carbon::parse($data['until'])->format('d M Y');
                            }
                            $indicators['created_at'] = $indicator;
                            
                            return $indicators;
                        }

                        // Label untuk preset standar
                        $labels = [
                            'today' => 'Hari Ini',
                            '7_days' => '7 Hari Terakhir',
                            '30_days' => '30 Hari Terakhir',
                            'this_month' => 'Bulan Ini',
                            'this_year' => 'Tahun Ini',
                            'last_year' => '1 Tahun Terakhir',
                        ];

                        $indicators['created_at'] = 'Waktu: ' . ($labels[$data['range']] ?? '');

                        return $indicators;
                    }),
            ])
            ->headerActions([
                Action::make('export_custom')
                    ->label('Download Laporan Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function ($livewire) {
                        // $livewire->tableFilters adalah array berisi nilai filter yang sedang aktif di layar user
                        return Excel::download(
                            new LaporanPendaftaranExport($livewire->tableFilters), 
                            'Laporan_Pendaftaran_' . date('d-m-Y') . '.xlsx'
                        );
                    }),
            ])
            ->actions([
                Action::make('print')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->color('success') // Warna Hijau
                ->url(fn (Registration $record) => route('admin.print.result', $record))
                ->openUrlInNewTab() // Buka di tab baru
                ->visible(fn (Registration $record) => $record->status === 'done'), // Hanya muncul jika status DONE
                
            // Action bawaan
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrations::route('/'),
            'create' => Pages\CreateRegistration::route('/create'),
            'edit' => Pages\EditRegistration::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            // Kita load relasi results dan parameter di awal agar ringan
            ->with(['patient', 'results.parameter']);
    }
}
