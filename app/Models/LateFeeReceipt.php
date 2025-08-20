<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class LateFeeReceipt extends Model
{
    protected $fillable = [
        'file_data',
        'file_name',
        'file_path',
        'file_size',
        'date',
        'status',
    ];

    const PENDING = 'pending';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
    const DONE = 'done';
    const ACCEPTED = 'accepted';
    const STATUS = [
        self::PENDING => 'Pending',
        self::APPROVED => 'Approved',
        self::REJECTED => 'Rejected',
        self::DONE => 'Done',
        self::ACCEPTED => 'Accepted',
    ];

    public function getFileNameWithExtensionAttribute()
    {
        return $this->file_name . '.' . pathinfo($this->file_path, PATHINFO_EXTENSION);
    }

    public function getIsPdfFileAttribute()
    {
        return pathinfo($this->file_path, PATHINFO_EXTENSION) === 'pdf';
    }

    protected function fileData(): Attribute
    {
        return Attribute::make(
            get: fn($value) => is_resource($value) ? stream_get_contents($value) : $value
        );
    }
}
