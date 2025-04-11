<?php

namespace App\Filament\Exports;

use App\Models\Report;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ReportExporter extends Exporter
{
    protected static ?string $model = Report::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('book_lending.visitor.identity_number')
                ->label('NIS Peminjam'),
            ExportColumn::make('book_lending.visitor.name')
                ->label('Nama Peminjam'),
            ExportColumn::make('book_lending.book.title')
                ->label('Judul Buku'),
            ExportColumn::make('book_lending.lending_date')
                ->label('Tanggal Peminjaman')
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('d/m/Y') : '-'),
            ExportColumn::make('book_lending.return_date')
                ->label('Tanggal Pengembalian')
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('d/m/Y') : '-'),
            ExportColumn::make('return_date')
                ->label('Dikembalikan Pada Tanggal')
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('d/m/Y') : '-'),
            ExportColumn::make('fine')
                ->label('Denda'),
            ExportColumn::make('status')
                ->label('Status')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your report export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
