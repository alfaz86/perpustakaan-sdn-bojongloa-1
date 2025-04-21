<?php

namespace App\Filament\Resources\VisitResource\Pages;

use App\Filament\Resources\VisitResource;
use App\Models\Visitor;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVisits extends ListRecords
{
    protected static string $resource = VisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Data')
                ->icon('heroicon-s-plus')
                ->mutateFormDataUsing(function (array $data): array {
                    if (empty($data['visitor_id'])) {
                        $visitor = Visitor::firstOrCreate([
                            'name' => $data['visitor']['name'],
                            'identity_number' => $data['visitor']['identity_number'],
                        ]);
    
                        $data['visitor_id'] = $visitor->id;
                    }

                    $data['visiting_time'] = now()->format('Y-m-d H:i:s');
    
                    return $data;
                }),
        ];
    }
}
