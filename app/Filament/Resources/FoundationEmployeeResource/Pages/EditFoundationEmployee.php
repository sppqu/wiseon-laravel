<?php

namespace App\Filament\Resources\FoundationEmployeeResource\Pages;

use App\Filament\Resources\FoundationEmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFoundationEmployee extends EditRecord
{
    protected static string $resource = FoundationEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
