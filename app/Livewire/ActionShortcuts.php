<?php

namespace App\Livewire;

use Filament\Facades\Filament;
use Livewire\Component;
use Filament\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Concerns\InteractsWithActions;

class ActionShortcuts extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function logout(): Action
    {
        return Action::make('logout')
            ->label('Logout')
            ->icon('heroicon-o-arrow-left-end-on-rectangle')
            ->color('danger')
            ->extraAttributes(['class' => 'w-full'])
            ->action(function () {
                Filament::auth()->logout();
    
                session()->invalidate();
                session()->regenerateToken();
    
                return redirect()->to(Filament::getLoginUrl());
            });
    }

    public function render(): string
    {
        return <<<'HTML'
            <div class="space-y-2">
                {{ $this->logout }}
            </div>
        HTML;
    }
}
