<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookLending extends Model
{
    use HasFactory;

    protected $fillable = ['visitor_id', 'book_id', 'lending_date', 'due_date'];

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
            $report = Report::where('book_lending_id', $bookLending->id)->first();

            if ($report && $report->status !== 'Belum Kembali') {
                throw new \Exception('Peminjaman tidak bisa dihapus karena sebagian peminjaman sudah masuk ke pencatatan laporan.');
            }

            $book = Book::find($bookLending->book_id);

            if ($book) {
                $book->update(['information' => 'available']);
            }

            if ($report) {
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

    public function report()
    {
        return $this->hasOne(Report::class);
    }
}
