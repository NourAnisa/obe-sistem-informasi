<?php

namespace App\Filament\Resources\BahanKajianResource\Pages;

use App\Filament\Resources\BahanKajianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBahanKajians extends ListRecords
{
    protected static string $resource = BahanKajianResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
