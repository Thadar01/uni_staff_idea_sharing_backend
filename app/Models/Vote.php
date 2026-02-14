<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $table = 'votes';

    protected $primaryKey = 'voteID';

    protected $fillable = [
        'voteType',
        'staffID',
        'ideaID'
    ];

    // Relationship: Vote belongs to Staff
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staffID', 'staffID');
    }

    // Relationship: Vote belongs to Idea
    public function idea()
    {
        return $this->belongsTo(Idea::class, 'ideaID', 'ideaID');
    }
}