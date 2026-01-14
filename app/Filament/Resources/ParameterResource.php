<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParameterResource\Pages;
use App\Filament\Resources\ParameterResource\RelationManagers;
use App\Models\Parameter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ParameterResource extends Resource
{
    protected static ?string $model = Parameter::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name') // Relasi ke tabel categories
                    ->required()
                    ->createOptionForm([ // Fitur Quick Create Kategori baru
                        Forms\Components\TextInput::make('name')->required(),
                    ]),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Pemeriksaan')
                    ->required(),
                Forms\Components\TextInput::make('unit')
                    ->label('Satuan (Unit)')
                    ->placeholder('Contoh: mg/dL'),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('ref_range_male')
                            ->label('Nilai Rujukan Pria'),
                        Forms\Components\TextInput::make('ref_range_female')
                            ->label('Nilai Rujukan Wanita'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pemeriksaan')
                    ->searchable() // Agar bisa dicari
                    ->sortable(),  // Agar bisa diurutkan
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListParameters::route('/'),
            'create' => Pages\CreateParameter::route('/create'),
            'edit' => Pages\EditParameter::route('/{record}/edit'),
        ];
    }
}
