<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookLendingResource\Pages;
use App\Models\Book;
use App\Models\BookLending;
use App\Models\Visitor;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BookLendingResource extends Resource
{
    protected static ?string $model = BookLending::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';

    protected static ?string $modelLabel = 'Pinjam Buku';

    protected static ?string $pluralModelLabel = 'Data Pinjam Buku';

    protected static ?string $navigationLabel = 'Pinjam Buku';

    protected static ?int $navigationSort = 2;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('visitor_id')
                    ->label('Nama Peminjam')
                    ->relationship('visitor', 'name')
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->name} - {$record->identity_number}")
                    ->searchable()
                    ->placeholder('Cari Pengunjung')
                    ->searchPrompt('Ketik Nama atau NIS Siswa...')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Pengunjung')
                            ->required(),
                        Forms\Components\TextInput::make('identity_number')
                            ->label('No Induk Siswa')
                            ->unique(Visitor::class, 'identity_number')
                            ->required(),
                    ])
                    ->required(),

                Forms\Components\Select::make('book_id')
                    ->label('Buku')
                    ->searchable()
                    ->placeholder('Cari Buku...')
                    ->getSearchResultsUsing(function (string $search) {
                        return Book::query()
                            ->where('title', 'like', "%{$search}%")
                            ->orWhere('information', 'like', "%{$search}%")
                            ->limit(10)
                            ->get()
                            ->mapWithKeys(function ($book) {
                                $status = $book->information === 'available' ? 'Tersedia' : 'Tidak Tersedia';
                                $color = $book->information === 'available' ? 'success' : 'danger';

                                $badge = <<<HTML
                                    <span style="--c-50:var(--{$color}-50);--c-400:var(--{$color}-400);--c-600:var(--{$color}-600);"
                                        class="fi-badge inline-flex items-center rounded-md text-xs font-medium ring-1 ring-inset px-2 py-0.5 fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30 fi-color-{$color}">
                                        <span>{$status}</span>
                                    </span>
                                HTML;

                                $label = <<<HTML
                                    <span class="flex items-center justify-between gap-2">
                                        <span>{$book->title}</span>
                                        {$badge}
                                    </span>
                                HTML;

                                return [
                                    $book->id => $label,
                                ];
                            })
                            ->toArray();
                    })
                    ->getOptionLabelUsing(fn($value) => Book::find($value)?->title ?? '-')
                    ->allowHtml()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $book = Book::find($state);
                        if ($book && $book->information === Book::NOT_AVAILABLE) {
                            Notification::make()
                                ->title('Buku tidak tersedia')
                                ->danger()
                                ->send();
                            $set('book_id', null);
                        }
                    }),

                Forms\Components\DatePicker::make('lending_date')
                    ->label('Tanggal Peminjaman')
                    ->default(now())
                    ->required()
                    ->reactive(),

                Forms\Components\DatePicker::make('due_date')
                    ->label('Batas Pengembalian')
                    ->minDate(function ($get) {
                        $lendingDate = $get('lending_date');
                        if (!$lendingDate) {
                            return null;
                        }
                        $lendingDate = Carbon::parse($lendingDate);
                        $dueDate = $lendingDate->addDays(1);
                        return $dueDate;
                    })
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(
                BookLending::query()
                    ->with([
                        'visitor',
                        'book' => fn($query) => $query->withTrashed(),
                    ])
                    ->orderBy('lending_date', 'desc')
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('visitor.name')
                    ->label('Nama Peminjam')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('book.title')
                    ->label('Judul Buku')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lending_date')
                    ->label('Tanggal Peminjaman')
                    ->date()
                    ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->format('d/m/Y') : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Batas Pengembalian')
                    ->date()
                    ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->format('d/m/Y') : '-')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make('deleteIfNotReturned')
                        ->label('Hapus')
                        ->requiresConfirmation()
                        ->action(fn(Collection $records) => self::handleBulkDelete($records))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookLendings::route('/'),
        ];
    }

    public static function handleBulkDelete(Collection $records): void
    {
        $deleted = 0;
        $skipped = 0;

        foreach ($records as $record) {
            if (optional($record->report)->status === 'Belum Kembali') {
                $record->delete();
                $deleted++;
            } else {
                $skipped++;
            }
        }

        $recipient = auth()->user();
        $status = 'success';
        $title = 'Hapus Data Selesai';
        $body = "Berhasil menghapus {$deleted} data.";

        if ($deleted > 0 && $skipped > 0) {
            $status = 'warning';
            $title = 'Hapus Data Selesai';
            $body = "Berhasil menghapus {$deleted} data. {$skipped} data dilewati karena sudah masuk ke pencatatan laporan.";
        }
        if ($deleted === 0 && $skipped > 0) {
            $status = 'danger';
            $title = 'Hapus Data Gagal';
            $body = "Tidak ada data yang dihapus. {$skipped} data dilewati karena sudah masuk ke pencatatan laporan.";
        }

        Notification::make()
            ->$status()
                ->title($title)
                ->body($body)
                ->send();

        Notification::make()
            ->$status()
                ->title($title)
                ->body($body)
                ->sendToDatabase($recipient);
    }
}
