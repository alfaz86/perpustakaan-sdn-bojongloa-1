<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class WelcomeWidget extends Widget
{
    protected static string $view = 'filament.pages.dashboard';

    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }

}