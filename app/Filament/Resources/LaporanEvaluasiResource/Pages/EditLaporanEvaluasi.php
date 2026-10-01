<?php

namespace App\Filament\Resources\LaporanEvaluasiResource\Pages;

use App\Filament\Resources\LaporanEvaluasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaporanEvaluasi extends EditRecord
{
    protected static string $resource = LaporanEvaluasiResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
