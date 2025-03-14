<?php

namespace App\Filament\Resources\BookLendingResource\Pages;

use App\Filament\Resources\BookLendingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookLending extends EditRecord
{
    protected static string $resource = BookLendingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
