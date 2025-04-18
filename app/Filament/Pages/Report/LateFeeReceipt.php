<?php

namespace App\Filament\Pages\Report;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use App\Models\LateFeeReceipt as LateFeeReceiptModel;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class LateFeeReceipt extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $model = LateFeeReceiptModel::class;

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

    public static function table(Table $table): Table
    {
        return $table
            ->query(LateFeeReceiptModel::query())
            ->recordUrl(null)
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('file_name')
                    ->label('Nama File')
                    ->searchable(),
                TextColumn::make('file_size')
                    ->label('Ukuran File')
                    ->formatStateUsing(fn ($state) => number_format($state / 1024, 2) . ' KB')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('date')
                    ->label('Tanggal Setoran')
                    ->sortable()
                    ->searchable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->searchable()
                    ->colors([
                        'danger' => fn($state) => $state === LateFeeReceiptModel::REJECTED,
                        'success' => fn($state) => in_array($state, [
                            LateFeeReceiptModel::ACCEPTED,
                            LateFeeReceiptModel::APPROVED,
                            LateFeeReceiptModel::DONE,
                        ]),
                        'warning' => fn($state) => in_array($state, [
                            LateFeeReceiptModel::PENDING,
                        ]),
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state)),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('download')
                    ->label('Download')
                    ->url(fn($record) => route('report.late-fee-receipt.download', $record))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->openUrlInNewTab(),
            ])
            ->headerActions([
                Action::make('uploadReceipt')
                    ->label('Upload Bukti Setoran')
                    ->modalHeading('Upload Bukti Setoran')
                    ->form([
                        FileUpload::make('file')
                            ->label('File')
                            ->required()
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'text/csv',
                            ])
                            ->mimeTypeMap([
                                'application/pdf' => 'pdf',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
                                'text/csv' => 'csv',
                            ])
                            ->maxSize(1024 * 10)
                            ->storeFiles()
                            ->preserveFilenames(),
                        DatePicker::make('date')
                            ->label('Tanggal Setoran')
                            ->required()
                            ->default(now()),
                    ])
                    ->action(function (array $data) {
                        self::setFileDatabase($data);
                        Notification::make()
                            ->title('Success')
                            ->body('Bukti setoran berhasil diunggah.')
                            ->success()
                            ->send();
                    })
                    ->color('primary')
                    ->icon('heroicon-m-plus')

            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function setFileDatabase($data): void
    {
        $path = $data['file'];

        if (Storage::disk('public')->exists($path)) {
            $fileData = Storage::disk('public')->get($path);
            $fileSize = Storage::disk('public')->size($path);
        } else {
            return;
        }

        LateFeeReceiptModel::create([
            'file_data' => $fileData,
            'file_name' => pathinfo($path, PATHINFO_FILENAME),
            'file_path' => pathinfo($path, PATHINFO_BASENAME),
            'file_size' => $fileSize,
            'date' => $data['date'],
            'status' => LateFeeReceiptModel::PENDING,
        ]);
    }
}