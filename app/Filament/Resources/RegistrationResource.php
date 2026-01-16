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
            ->columns([
                Tables\Columns\TextColumn::make('registration_number')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('patient.nik')->label('Nik')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('patient.name')->label('Pasien')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Tanggal')->sortable()->dateTime(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'processing',
                        'success' => 'done',
                    ]),
            ])
            ->filters([
                // --- MULAI KODE FILTER ---
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Pemeriksaan') // Label yang muncul di UI
                    ->options([
                        'pending' => 'Menunggu Sampel',
                        'processing' => 'Sedang Diperiksa',
                        'done' => 'Selesai (Valid)',
                    ]),
                // --- SELESAI KODE FILTER ---
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
}
