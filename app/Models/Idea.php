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

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staffID', 'staffID');
    }

    public function closureSetting()
    {
        return $this->belongsTo(ClosureSetting::class, 'settingID', 'settingID');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'ideaID', 'ideaID');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class, 'ideaID', 'ideaID');
    }

    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'idea_category',
            'ideaID',
            'categoryID'
        );
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'ideaID', 'ideaID');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'ideaID', 'ideaID');
    }
}