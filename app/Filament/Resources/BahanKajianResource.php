<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BahanKajianResource\Pages;
use App\Models\BahanKajian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BahanKajianResource extends Resource
{
    protected static ?string $model = BahanKajian::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationGroup = 'Kurikulum OBE';
    protected static ?int $navigationSort = 5;
    protected static ?string $label = 'Bahan Kajian';
    protected static ?string $pluralLabel = 'Bahan Kajian';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('kode')
                ->maxLength(50)
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('nama')
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('referensi')
                ->label('Referensi / Deskripsi')
                ->rows(3)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()->limit(60)->wrap(),
                Tables\Columns\TextColumn::make('mataKuliahs_count')
                    ->label('Mata Kuliah')
                    ->counts('mataKuliahs')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('cpls_count')
                    ->label('CPL')
                    ->counts('cpls')
                    ->alignRight(),
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
            'index'  => Pages\ListBahanKajians::route('/'),
            'create' => Pages\CreateBahanKajian::route('/create'),
            'edit'   => Pages\EditBahanKajian::route('/{record}/edit'),
        ];
    }
}
