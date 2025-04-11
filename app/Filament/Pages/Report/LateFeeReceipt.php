<?php

namespace App\Filament\Pages\Report;

use Filament\Pages\Page;

class LateFeeReceipt extends Page
{
    protected static string $view = 'filament.pages.late-fee-receipt';

    protected static ?string $title = 'Laporan';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $slug = 'report/late-fee-receipt';

    protected static bool $shouldRegisterNavigation = false;

    public function getBreadcrumbs(): array
    {
        return [
            route(static::getRouteName()) => 'Laporan',
            'Bukti Setoran',
        ];
    }
}