<?php

namespace App\Filament\Resources\CpmkResource\Pages;

use App\Filament\Resources\CpmkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCpmks extends ListRecords
{
    protected static string $resource = CpmkResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
