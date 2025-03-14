<?php

namespace App\Filament\Resources\BookLendingResource\Pages;

use App\Filament\Resources\BookLendingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBookLending extends CreateRecord
{
    protected static string $resource = BookLendingResource::class;
}
