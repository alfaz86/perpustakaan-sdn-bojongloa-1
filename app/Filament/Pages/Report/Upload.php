<?php

namespace App\Filament\Pages\Report;

use Filament\Pages\Page;

class Upload extends Page
{
    protected static string $view = 'filament.pages.upload';

    protected static ?string $title = 'Laporan';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $slug = 'report/upload';

    protected static bool $shouldRegisterNavigation = false;

    public function getBreadcrumbs(): array
    {
        return [
            route(static::getRouteName()) => 'Laporan',
            'Upload',
        ];
    }
}