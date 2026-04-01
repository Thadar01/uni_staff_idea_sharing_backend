<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';
    protected $primaryKey = 'log_id';

    protected $fillable = [
        'user_id',
        'url',
        'method',
        'user_agent',
        'browser'
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'user_id', 'staffID');
    }
}
