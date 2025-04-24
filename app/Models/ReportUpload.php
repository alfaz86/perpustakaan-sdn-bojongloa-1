<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportUpload extends Model
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
}
