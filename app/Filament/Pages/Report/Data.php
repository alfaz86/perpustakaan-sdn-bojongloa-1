<?php

namespace App\Filament\Pages\Report;

use App\Filament\Exports\ReportExporter;
use App\Models\Report;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class Data extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $model = Report::class;

    protected static string $view = 'filament.pages.report';

    protected static ?string $title = 'Laporan';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Laporan';

    protected static ?string $slug = 'report';

    protected static array $subMenuItems = [];

    public function getBreadcrumbs(): array
    {
        return [
            route(static::getRouteName()) => 'Laporan',
            'List',
        ];
    }

    public static function getSubMenuItems(): array
    {
        return [
            [
                'label' => '📄 Data Laporan',
                'route' => Data::getRouteName(),
            ],
            [
                'label' => '📄 Bukti Setoran',
                'route' => LateFeeReceipt::getRouteName(),
            ],
            [
                'label' => '📄 Upload Laporan',
                'route' => Upload::getRouteName(),
            ],
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Report::with(['book_lending.visitor', 'book_lending.book']))
            ->recordUrl(null)
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('book_lending.visitor.identity_number')
                    ->label('NIS Peminjam')
                    ->searchable(),
                TextColumn::make('book_lending.visitor.name')
                    ->label('Nama Peminjam')
                    ->searchable(),
                TextColumn::make('book_lending.book.title')
                    ->label('Judul Buku')
                    ->searchable(),
                TextColumn::make('book_lending.lending_date')
                    ->label('Tanggal Peminjaman')
                    ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->format('d/m/Y') : '-'),
                TextColumn::make('book_lending.return_date')
                    ->label('Tanggal Pengembalian')
                    ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->format('d/m/Y') : '-'),
                TextColumn::make('returned_on_date')
                    ->label('Dikembalikan Pada Tanggal'),
                TextColumn::make('fine')
                    ->label('Denda'),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => fn($state) => $state === 'Sudah Kembali',
                        'danger' => fn($state) => in_array($state, ['Terlambat', 'Belum Kembali']),
                    ]),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Pengembalian')
                    ->options([
                        'Belum Kembali' => 'Belum Kembali',
                        'Sudah Kembali' => 'Sudah Kembali',
                        'Terlambat' => 'Terlambat',
                    ])
                    ->query(function (Builder $query, $state) {

                        if (!$state['value'])
                            return;

                        $query->whereHas('book_lending', function ($q) use ($state) {
                            $q->where(function ($subQuery) use ($state) {
                                if ($state['value'] === 'Belum Kembali') {
                                    $subQuery->whereNull('reports.return_date');
                                }

                                if ($state['value'] === 'Sudah Kembali') {
                                    $subQuery->whereNotNull('reports.return_date')
                                        ->whereRaw('reports.return_date BETWEEN book_lendings.lending_date AND book_lendings.return_date');
                                }

                                if ($state['value'] === 'Terlambat') {
                                    $subQuery->whereNotNull('reports.return_date')
                                        ->whereRaw('reports.return_date > book_lendings.return_date');
                                }
                            });
                        });
                    }),
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->actions([
                EditAction::make()
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('return_date')
                            ->label('Dikembalikan Pada Tanggal')
                            ->required()
                            ->minDate(today())
                            ->maxDate(today())
                            ->default(today()),
                    ])
                    ->after(function (Report $record, array $data) {
                        $record->update([
                            'return_date' => $data['return_date'],
                        ]);
                    }),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(ReportExporter::class)
                    ->fileName('data-laporan-' . now()->timestamp)
                    ->label('Ekspor Laporan')
                    ->color('primary'),

                Action::make('total-fine')
                    ->label('Total Denda: ' . Report::getTotalFineFormatted())
                    ->color('gray')
                    ->action(fn() => null)
                    ->extraAttributes([
                        'style' => 'cursor: default; pointer-events: none; font-weight: bold;',
                    ])
            ]);
    }
}