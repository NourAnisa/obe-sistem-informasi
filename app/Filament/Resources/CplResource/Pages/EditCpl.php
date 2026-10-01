<?php

namespace App\Filament\Resources\CplResource\Pages;

use App\Filament\Resources\CplResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCpl extends EditRecord
{
    protected static string $resource = CplResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
