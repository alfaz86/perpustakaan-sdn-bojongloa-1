<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\WelcomeWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Perpustakaan SDN Bojongloa 1';

    protected static ?string $navigationLabel = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            WelcomeWidget::class,
        ];
    }
}