<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Manajemen';
    protected static ?int $navigationSort = 10;
    protected static ?string $label = 'Pengguna';
    protected static ?string $pluralLabel = 'Pengguna';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Akun')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->options([
                        'admin'         => '👑 Admin',
                        'dekan'         => '🏛 Dekan',
                        'wakildekan'    => '🏫 Wakil Dekan',
                        'kaprodi'       => '🎓 Kaprodi',
                        'dosen'         => '📚 Dosen',
                        'akademik'      => '🗂 Akademik',
                        'kemahasiswaan' => '🤝 Kemahasiswaan',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->maxLength(255),
            ])->columns(2),
            Forms\Components\Section::make('Informasi Identitas')->schema([
                Forms\Components\TextInput::make('jabatan')->maxLength(255),
                Forms\Components\TextInput::make('nik')->label('NIK')->maxLength(50),
                Forms\Components\TextInput::make('nuptk')->label('NUPTK')->maxLength(50),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn(string $value): string => match ($value) {
                        'admin'         => 'danger',
                        'dekan'         => 'warning',
                        'wakildekan'    => 'warning',
                        'kaprodi'       => 'primary',
                        'dosen'         => 'success',
                        'akademik'      => 'info',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => \App\Models\User::roleLabels()[$state] ?? $state),
                Tables\Columns\TextColumn::make('jabatan')->limit(30)->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y')->sortable()->label('Dibuat'),
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
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
