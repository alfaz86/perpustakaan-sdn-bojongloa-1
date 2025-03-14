<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookLendingResource\Pages;
use App\Models\Book;
use App\Models\BookLending;
use App\Models\Visitor;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;

class BookLendingResource extends Resource
{
    protected static ?string $model = BookLending::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';

    protected static ?string $modelLabel = 'Pinjam Buku';

    protected static ?string $pluralModelLabel = 'Data Pinjam Buku';

    protected static ?string $navigationLabel = 'Pinjam Buku';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('visitor_id')
                    ->label('Nama Peminjam')
                    ->relationship('visitor', 'name')
                    ->searchable()
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
                    ->relationship('book', 'title')
                    ->searchable()
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
                    ->required(),

                Forms\Components\DatePicker::make('return_date')
                    ->label('Tanggal Pengembalian')
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('visitor.name')
                    ->label('Nama Peminjam')
                    ->searchable(),
                Tables\Columns\TextColumn::make('book.title')
                    ->label('Judul Buku')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lending_date')
                    ->label('Tanggal Peminjaman')
                    ->date(),
                Tables\Columns\TextColumn::make('return_date')
                    ->label('Tanggal Pengembalian')
                    ->date(),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookLendings::route('/'),
        ];
    }
}
