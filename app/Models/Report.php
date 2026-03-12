<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $table = 'reports';
    protected $primaryKey = 'report_id';

    protected $fillable = [
        'report_type',
        'reason',
        'status',
        'ideaID',
        'commentID',
        'reporter_id',
        'resolved_by'
    ];

    // Relationship: Reporter
    public function reporter()
    {
        return $this->belongsTo(Staff::class, 'reporter_id', 'staffID');
    }

    // Relationship: Resolved by staff
    public function resolver()
    {
        return $this->belongsTo(Staff::class, 'resolved_by', 'staffID');
    }

    // Relationship: Report belongs to an idea
    public function idea()
    {
        return $this->belongsTo(Idea::class, 'ideaID', 'ideaID');
    }

    // Relationship: Report belongs to a comment
    public function comment()
    {
        return $this->belongsTo(Comment::class, 'commentID', 'commentID');
    }
}