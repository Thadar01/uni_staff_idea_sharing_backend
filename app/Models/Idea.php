<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Idea extends Model
{
    use HasFactory;

    protected $table = 'idea';

    protected $primaryKey = 'ideaID';

    protected $fillable = [
        'title',
        'description',
        'isAnonymous',
        'staffID',
        'settingID',
        'status',
        'viewCount',
        'isFlagged',
        'isCommentEnabled'
    ];

    // Relationship: Idea belongs to Staff
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staffID', 'staffID');
    }

    // Relationship: Idea belongs to ClosureSetting
    public function closureSetting()
    {
        return $this->belongsTo(ClosureSetting::class, 'settingID', 'settingID');
    }

    // Relationship: Idea has many Comments
    public function comments()
    {
        return $this->hasMany(Comment::class, 'ideaID', 'ideaID');
    }

    // Relationship: Idea has many Votes
    public function votes()
    {
        return $this->hasMany(Vote::class, 'ideaID', 'ideaID');
    }

    // Relationship: Idea belongs to many Categories (pivot table)
    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'idea_category',
            'ideaID',
            'categoryID'
        );
    }
}