<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'visitor_id',
        'visiting_time',
    ];

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }
}
