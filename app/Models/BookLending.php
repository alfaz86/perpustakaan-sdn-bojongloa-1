<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookLending extends Model
{
    use HasFactory;

    protected $fillable = ['visitor_id', 'book_id', 'lending_date', 'return_date'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bookLending) {
            $book = Book::where('id', $bookLending->book_id);

            if ($book->exists()) {
                $book->update(['information' => 'not available']);
            }
        });

        static::created(function ($bookLending) {
            Report::create([
                'book_lending_id' => $bookLending->id,
                'return_date' => null,
            ]);
        });

        static::deleting(function ($bookLending) {
            $book = Book::where('id', $bookLending->book_id);
            $report = Report::where('book_lending_id', $bookLending->id);

            if ($book->exists()) {
                $book->update(['information' => 'available']);
            }
            if ($report->exists()) {
                $report->delete();
            }
        });
    }

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
