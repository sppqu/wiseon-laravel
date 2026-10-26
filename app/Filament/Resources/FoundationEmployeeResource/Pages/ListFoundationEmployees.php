<?php

namespace App\Filament\Resources\FoundationEmployeeResource\Pages;

use App\Filament\Resources\FoundationEmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFoundationEmployees extends ListRecords
{
    protected static string $resource = FoundationEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
