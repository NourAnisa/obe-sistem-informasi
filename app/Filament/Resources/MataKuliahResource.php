<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MataKuliahResource\Pages;
use App\Models\MataKuliah;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MataKuliahResource extends Resource
{
    protected static ?string $model = MataKuliah::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Kurikulum OBE';
    protected static ?int $navigationSort = 2;
    protected static ?string $label = 'Mata Kuliah';
    protected static ?string $pluralLabel = 'Mata Kuliah';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas')->schema([
                Forms\Components\TextInput::make('kode')->required()->maxLength(50)->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('nama')->required()->maxLength(255)->columnSpan(2),
                Forms\Components\TextInput::make('sks')->numeric()->required()->minValue(1)->maxValue(10),
                Forms\Components\TextInput::make('semester')->numeric()->required()->minValue(1)->maxValue(8),
                Forms\Components\Select::make('kategori')
                    ->options([
                        'MKF'  => 'MKF',
                        'MKPU' => 'MKPU',
                        'MKWK' => 'MKWK',
                        'MKPP' => 'MKPP',
                        'MKP'  => 'MKP',
                        'MKKP' => 'MKKP',
                    ])
                    ->required(),
            ])->columns(3),
            Forms\Components\Section::make('Detail')->schema([
                Forms\Components\TextInput::make('pjmk')->label('Dosen PJMK')->maxLength(255),
                Forms\Components\Toggle::make('is_wajib')->label('MK Wajib'),
                Forms\Components\Toggle::make('is_mbkm')->label('MK MBKM'),
                Forms\Components\Textarea::make('deskripsi')->rows(3)->columnSpanFull(),
                Forms\Components\Textarea::make('deskripsi_en')->label('Deskripsi (English)')->rows(3)->columnSpanFull(),
            ])->columns(3),
            Forms\Components\Section::make('CPL yang Dibebankan')->schema([
                Forms\Components\CheckboxList::make('cpls')
                    ->relationship('cpls', 'kode')
                    ->getOptionLabelFromRecordUsing(
                        fn($record) => "{$record->kode} — " . \Illuminate\Support\Str::limit($record->deskripsi, 60)
                    )
                    ->columns(2)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('nama')->searchable()->limit(45)->wrap(),
                Tables\Columns\TextColumn::make('semester')->sortable()->alignCenter()->badge()->color('primary'),
                Tables\Columns\TextColumn::make('sks')->sortable()->alignCenter()->badge()->color('gray'),
                Tables\Columns\TextColumn::make('kategori')
                    ->badge()
                    ->color(fn(string $value): string => match($value) {
                        'MKF' => 'primary', 'MKWK' => 'success', 'MKPP' => 'warning', 'MKP' => 'danger', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('pjmk')->label('PJMK')->limit(25)->searchable(),
                Tables\Columns\IconColumn::make('is_mbkm')->label('MBKM')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('semester')
                    ->options(array_combine(range(1, 8), array_map(fn($s) => "Semester $s", range(1, 8)))),
                Tables\Filters\SelectFilter::make('kategori')
                    ->options([
                        'MKF'  => 'MKF',
                        'MKPU' => 'MKPU',
                        'MKWK' => 'MKWK',
                        'MKPP' => 'MKPP',
                        'MKP'  => 'MKP',
                        'MKKP' => 'MKKP',
                    ]),
                Tables\Filters\TernaryFilter::make('is_mbkm')->label('MK MBKM'),
            ])
            ->defaultSort('semester')
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
            'index'  => Pages\ListMataKuliahs::route('/'),
            'create' => Pages\CreateMataKuliah::route('/create'),
            'edit'   => Pages\EditMataKuliah::route('/{record}/edit'),
        ];
    }
}
