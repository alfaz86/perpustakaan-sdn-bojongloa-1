<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_lending_id',
        'return_date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($report) {
            $book = Book::where('id', $report->book_lending->book_id);

            if ($book->exists()) {
                $book->update(['information' => 'available']);
            }
        });
    }

    public function book_lending()
    {
        return $this->belongsTo(BookLending::class);
    }

    public function getReturnedOnDateAttribute()
    {
        return $this->return_date ? Carbon::parse($this->return_date)->format('d/m/Y') : '-';
    }

    public function getStatusAttribute()
    {
        $bookLendingDueDate = $this->book_lending->due_date;
        $bookLendingStartDate = $this->book_lending->lending_date;
        $reportReturnDate = $this->return_date;
        
        $actualReturn = Carbon::parse($reportReturnDate);
        $start = Carbon::parse($bookLendingStartDate);
        $due = Carbon::parse($bookLendingDueDate);

        if (is_null($reportReturnDate)) {
            if (Carbon::now()->between($start, $due)) {
                return 'Dipinjam';
            }
        }

        if ($reportReturnDate) {
            if ($actualReturn->between($start, $due)) {
                return 'Sudah Kembali';
            }

            if ($actualReturn->gt($due)) {
                return 'Terlambat';
            }
        }

        return 'Belum Kembali';
    }

    public function getFineAttribute()
    {
        $status = $this->status;

        if ($status === 'Terlambat') {
            return 'Rp 3.000';
        }

        return '-';
    }

    public static function getTotalFine(): int
    {
        $terlambatCount = static::all()->filter(fn($report) => $report->status === 'Terlambat')->count();

        return $terlambatCount * 3000;
    }

    public static function getTotalFineFormatted(): string
    {
        return 'Rp ' . number_format(static::getTotalFine(), 0, ',', '.');
    }
}
