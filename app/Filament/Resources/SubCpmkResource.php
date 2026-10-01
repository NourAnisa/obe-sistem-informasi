<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubCpmkResource\Pages;
use App\Models\SubCpmk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SubCpmkResource extends Resource
{
    protected static ?string $model = SubCpmk::class;
    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationGroup = 'Kurikulum OBE';
    protected static ?int $navigationSort = 4;
    protected static ?string $label = 'Sub-CPMK';
    protected static ?string $pluralLabel = 'Sub-CPMK';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('cpmk_id')
                ->label('CPMK Induk')
                ->relationship('cpmk', 'kode')
                ->getOptionLabelFromRecordUsing(
                    fn($r) => "{$r->kode} — " . Str::limit($r->deskripsi, 50)
                )
                ->searchable()->required(),
            Forms\Components\TextInput::make('kode')->required()->maxLength(20)->unique(ignoreRecord: true),
            Forms\Components\Textarea::make('deskripsi')->required()->rows(3)->columnSpanFull(),
            Forms\Components\Textarea::make('deskripsi_en')->label('Deskripsi (English)')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('cpmk.kode')->label('CPMK')->badge()->color('warning'),
                Tables\Columns\TextColumn::make('cpmk.cpl.kode')->label('CPL')->badge()->color('primary'),
                Tables\Columns\TextColumn::make('deskripsi')->limit(70)->searchable()->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cpmk')->relationship('cpmk', 'kode'),
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
            'index'  => Pages\ListSubCpmks::route('/'),
            'create' => Pages\CreateSubCpmk::route('/create'),
            'edit'   => Pages\EditSubCpmk::route('/{record}/edit'),
        ];
    }
}
