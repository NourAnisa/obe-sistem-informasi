<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanEvaluasiResource\Pages;
use App\Models\LaporanEvaluasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LaporanEvaluasiResource extends Resource
{
    protected static ?string $model = LaporanEvaluasi::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Evaluasi';
    protected static ?int $navigationSort = 20;
    protected static ?string $label = 'Laporan Evaluasi';
    protected static ?string $pluralLabel = 'Laporan Evaluasi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas Laporan')->schema([
                Forms\Components\Select::make('mata_kuliah_id')
                    ->label('Mata Kuliah')
                    ->relationship('mataKuliah', 'nama')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('semester')
                    ->options(['Ganjil' => 'Ganjil', 'Genap' => 'Genap'])
                    ->required(),
                Forms\Components\TextInput::make('tahun_akademik')
                    ->label('Tahun Akademik')
                    ->placeholder('2024/2025')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('kelas')
                    ->maxLength(10),
                Forms\Components\TextInput::make('jumlah_mahasiswa')
                    ->label('Jumlah Mahasiswa')
                    ->numeric()
                    ->minValue(0),
                Forms\Components\TextInput::make('dosen_pjmk')
                    ->label('Dosen PJMK')
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'final' => 'Final'])
                    ->default('draft')
                    ->required(),
            ])->columns(3),
            Forms\Components\Section::make('Catatan')->schema([
                Forms\Components\Textarea::make('catatan_umum')
                    ->label('Catatan Umum')
                    ->rows(4)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mataKuliah.kode')
                    ->label('Kode MK')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('mataKuliah.nama')
                    ->label('Mata Kuliah')
                    ->searchable()
                    ->limit(35),
                Tables\Columns\TextColumn::make('semester')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('tahun_akademik')
                    ->label('Tahun Akademik')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_mahasiswa')
                    ->label('Mhs')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $value): string => match($value) {
                        'final' => 'success', default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index'  => Pages\ListLaporanEvaluasis::route('/'),
            'create' => Pages\CreateLaporanEvaluasi::route('/create'),
            'edit'   => Pages\EditLaporanEvaluasi::route('/{record}/edit'),
        ];
    }
}
