<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = [
        'name',
        'identity_number',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($visitor) {
            $bookLendings = BookLending::with('book')
                ->where('visitor_id', $visitor->id)
                ->get();

            if ($bookLendings->count() > 0) {
                foreach ($bookLendings as $bookLending) {
                    if ($bookLending->book) {
                        $bookLending->book->update(['information' => 'available']);
                    }
                }

                $bookLendings->each->delete();
            }
        });

    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }
}
