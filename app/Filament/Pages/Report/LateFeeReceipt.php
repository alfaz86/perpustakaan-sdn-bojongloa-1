<?php

namespace App\Filament\Pages\Report;

use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use App\Models\LateFeeReceipt as LateFeeReceiptModel;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
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
                TextColumn::make('file_name_with_extension')
                    ->label('Nama File')
                    ->searchable(),
                TextColumn::make('file_size')
                    ->label('Ukuran File')
                    ->formatStateUsing(fn($state) => number_format($state / 1024, 2) . ' KB')
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
                ActionGroup::make([
                    Action::make('download')
                        ->label('Download')
                        ->url(fn($record) => route('report.late-fee-receipt', [
                            $record,
                            'type' => 'download',
                        ]))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->openUrlInNewTab(),
                    Action::make('preview')
                        ->label('Preview Pdf')
                        ->url(fn($record) => route('report.late-fee-receipt', [
                            $record,
                            'type' => 'preview',
                        ]))
                        ->icon('heroicon-o-eye')
                        ->color(fn($record) => $record->is_pdf_file ? Color::Amber : 'gray')
                        ->disabled(fn($record) => !$record->is_pdf_file)
                        ->openUrlInNewTab(),
                    Action::make('changeStatus')
                        ->label('Edit')
                        ->icon('heroicon-o-pencil')
                        ->color('primary')
                        ->modalHeading(__('Change Status'))
                        ->form([
                            Select::make('changeStatus')
                                ->label('Status')
                                ->options([
                                    LateFeeReceiptModel::PENDING => ucfirst(LateFeeReceiptModel::PENDING),
                                    LateFeeReceiptModel::APPROVED => ucfirst(LateFeeReceiptModel::APPROVED),
                                    LateFeeReceiptModel::REJECTED => ucfirst(LateFeeReceiptModel::REJECTED),
                                ])
                                ->default(function ($record) {
                                    return $record->status;
                                })
                                ->required()
                                ->native(false),
                        ])
                        ->action(function ($record, array $data) {
                            $newStatus = $data['changeStatus'];
                            $record->update([
                                'status' => $newStatus,
                            ]);

                            Notification::make()
                                ->title('Status Berhasil Diperbarui')
                                ->success()
                                ->send();
                        })
                        ->hidden(
                            function ($record) {
                                if (auth()->user()->role === User::ROLE_ADMIN) {
                                    return true;
                                }

                                if ($record->status !== LateFeeReceiptModel::PENDING) {
                                    return true;
                                }

                                return false;
                            }
                        ),
                ])
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
            if (DB::getDriverName() === 'pgsql') {
                $fileData = fopen(Storage::disk('public')->path($path), 'rb');
            } else {
                $fileData = Storage::disk('public')->get($path);
            }
            $fileSize = Storage::disk('public')->size($path);
        } else {
            return;
        }

        LateFeeReceiptModel::create([
            'file_data' => $fileData,
            'file_name' => pathinfo($path, PATHINFO_FILENAME),
            'file_path' => pathinfo($path, PATHINFO_BASENAME),
            'file_size' => $fileSize,
            'date' => now(),
            'status' => LateFeeReceiptModel::PENDING,
        ]);
    }
}
