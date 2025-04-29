<?php

namespace App\Filament\Resources\BookLendingResource\Pages;

use App\Filament\Resources\BookLendingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookLendings extends ListRecords
{
    protected static string $resource = BookLendingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-s-plus'),
        ];
    }
}
