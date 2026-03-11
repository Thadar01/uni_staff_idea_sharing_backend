<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments';

    protected $primaryKey = 'commentID';

    protected $fillable = [
        'comment',
        'isAnonymous',
        'status',
        'ideaID',
        'staffID'
    ];

    // Relationship: Comment belongs to Idea
    public function idea()
    {
        return $this->belongsTo(Idea::class, 'ideaID', 'ideaID');
    }

    // Relationship: Comment belongs to Staff
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staffID', 'staffID');
    }
}