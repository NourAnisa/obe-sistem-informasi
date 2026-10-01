<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CplResource\Pages;
use App\Models\Cpl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CplResource extends Resource
{
    protected static ?string $model = Cpl::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Kurikulum OBE';
    protected static ?int $navigationSort = 1;
    protected static ?string $label = 'CPL';
    protected static ?string $pluralLabel = 'Capaian Pembelajaran Lulusan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas CPL')->schema([
                Forms\Components\TextInput::make('kode')
                    ->required()->maxLength(20)->unique(ignoreRecord: true)
                    ->placeholder('CPL01'),
                Forms\Components\Select::make('kategori')
                    ->options([
                        'Sikap'    => 'Sikap',
                        'KU'       => 'Keterampilan Umum (KU)',
                        'KK'       => 'Keterampilan Khusus (KK)',
                        'PP'       => 'Penguasaan Pengetahuan (PP)',
                        'Sikap_KU' => 'Sikap & KU',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('total_skor_maks')
                    ->numeric()->default(0)->label('Total Skor Maksimal'),
            ])->columns(3),
            Forms\Components\Section::make('Deskripsi')->schema([
                Forms\Components\Textarea::make('deskripsi')->required()->rows(4)->columnSpanFull(),
                Forms\Components\Textarea::make('deskripsi_en')->label('Deskripsi (English)')->rows(4)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('kategori')
                    ->badge()
                    ->color(fn(string $value): string => match($value) {
                        'Sikap' => 'danger', 'KU' => 'success', 'KK' => 'primary', 'PP' => 'warning', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('deskripsi')->limit(80)->searchable()->wrap(),
                Tables\Columns\TextColumn::make('total_skor_maks')->label('Skor Maks')->sortable()->alignRight(),
                Tables\Columns\TextColumn::make('cpmks_count')->label('CPMK')->counts('cpmks')->alignRight(),
                Tables\Columns\TextColumn::make('mataKuliahs_count')->label('MK')->counts('mataKuliahs')->alignRight(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->options([
                        'Sikap'    => 'Sikap',
                        'KU'       => 'KU',
                        'KK'       => 'KK',
                        'PP'       => 'PP',
                        'Sikap_KU' => 'Sikap_KU',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCpls::route('/'),
            'create' => Pages\CreateCpl::route('/create'),
            'edit'   => Pages\EditCpl::route('/{record}/edit'),
        ];
    }
}
