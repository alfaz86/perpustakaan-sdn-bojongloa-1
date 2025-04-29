<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $modelLabel = 'Buku';

    protected static ?string $pluralModelLabel = 'Data Buku';

    protected static ?string $navigationLabel = 'Data Buku';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('code')
                    ->label('ID Buku')
                    ->required()
                    ->unique(Book::class, 'code', ignorable: fn($record) => $record ?? null)
                    ->maxLength(10),
                TextInput::make('title')
                    ->label('Judul Buku')
                    ->required()
                    ->maxLength(255),
                TextInput::make('author')
                    ->label('Pengarang')
                    ->required()
                    ->maxLength(255),
                TextInput::make('publication_year')
                    ->label('Tahun Terbit')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(date('Y')),
                Select::make('information')
                    ->label('Keterangan')
                    ->options([
                        Book::AVAILABLE => __(ucwords(Book::AVAILABLE)),
                        Book::NOT_AVAILABLE => __(ucwords(Book::NOT_AVAILABLE)),
                    ])
                    ->required()
                    ->native(false),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('code')
                    ->label('ID Buku')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Judul Buku')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('author')
                    ->label('Pengarang')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('publication_year')
                    ->label('Tahun Terbit')
                    ->sortable()
                    ->searchable(),
                BadgeColumn::make('information')
                    ->label('Keterangan')
                    ->sortable()
                    ->searchable()
                    ->colors([
                        'primary',
                        'success' => Book::AVAILABLE,
                        'danger' => Book::NOT_AVAILABLE,
                    ])
                    ->formatStateUsing(fn(string $state): string => __(ucwords($state)) ?? $state)
                    ->tooltip(function ($record) {
                        $bookLending = $record->book_lendings()
                            ->whereHas('report', fn($query) => $query->whereNull('return_date'))
                            ->latest()
                            ->first();

                        return $bookLending && $bookLending->visitor
                            ? __("Being borrowed by") . ": " . $bookLending->visitor->name
                            : null;
                    }),

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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}
