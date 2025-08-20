<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitResource\Pages;
use App\Filament\Resources\VisitResource\RelationManagers;
use App\Models\Visit;
use App\Models\Visitor;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VisitResource extends Resource
{
    protected static ?string $model = Visit::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $modelLabel = 'Kunjungan';

    protected static ?string $pluralModelLabel = 'Data Kunjungan';

    protected static ?string $navigationLabel = 'Data Kunjungan';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(12)->schema([
                Select::make('visitor_id')
                    ->label('Cari Data Pengunjung Lama')
                    ->searchable()
                    ->placeholder('Cari Pengunjung')
                    ->searchPrompt('Ketik Nama atau NIS Siswa...')
                    ->getSearchResultsUsing(function (?string $search) {
                        return Visitor::query()
                            ->when($search, function ($query, $search) {
                                $query->where('name', config('database.like_operator'), "%{$search}%")
                                    ->orWhere('identity_number', config('database.like_operator'), "%{$search}%");
                            })
                            ->limit(10)
                            ->get()
                            ->mapWithKeys(function ($visitor) {
                                return [
                                    $visitor->id => "{$visitor->name} - {$visitor->identity_number}",
                                ];
                            });
                    })
                    ->getOptionLabelUsing(fn($value) => Visitor::find($value)?->name)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $visitor = Visitor::find($state);
                        if ($visitor) {
                            $set('visitor.identity_number', $visitor->identity_number);
                            $set('visitor.name', $visitor->name);
                        }
                    })
                    ->columnSpan(6),
            ]),

            TextInput::make('visitor.name')
                ->label('Nama Pengunjung')
                ->required()
                ->maxLength(255),

            TextInput::make('visitor.identity_number')
                ->label('No Induk Siswa')
                ->required()
                ->maxLength(20)
                ->rule(function (Get $get) {
                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                        $visitorId = $get('visitor_id');

                        // Hanya jalankan validasi jika visitor_id kosong (pengunjung baru)
                        if (empty($visitorId)) {
                            $exists = Visitor::where('identity_number', $value)->exists();

                            if ($exists) {
                                $fail(__('The identity number has already been taken.'));
                            }
                        }
                    };
                })
            ,

            DatePicker::make('visiting_time')
                ->label('Waktu Kunjungan')
                ->default(now())
                ->minDate(now()->startOfDay())
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Visit::query()
                ->with('visitor')
                ->orderBy('visiting_time', 'desc'))
            ->columns([
                TextColumn::make('visitor.name')
                    ->label('Nama Pengunjung')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('visitor.identity_number')
                    ->label('No Induk Siswa')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('visiting_time')
                    ->label('Waktu Kunjungan')
                    ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->format('d/m/Y') : '-')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisits::route('/'),
        ];
    }
}
