<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CpmkResource\Pages;
use App\Models\Cpmk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CpmkResource extends Resource
{
    protected static ?string $model = Cpmk::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Kurikulum OBE';
    protected static ?int $navigationSort = 3;
    protected static ?string $label = 'CPMK';
    protected static ?string $pluralLabel = 'CPMK';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('cpl_id')
                ->label('CPL Induk')
                ->relationship('cpl', 'kode')
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
                Tables\Columns\TextColumn::make('cpl.kode')->label('CPL')->badge()->color('primary'),
                Tables\Columns\TextColumn::make('deskripsi')->limit(70)->searchable()->wrap(),
                Tables\Columns\TextColumn::make('subCpmks_count')->label('Sub-CPMK')->counts('subCpmks')->alignRight(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cpl')->relationship('cpl', 'kode'),
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
            'index'  => Pages\ListCpmks::route('/'),
            'create' => Pages\CreateCpmk::route('/create'),
            'edit'   => Pages\EditCpmk::route('/{record}/edit'),
        ];
    }
}
