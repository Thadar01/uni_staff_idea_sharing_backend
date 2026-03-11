<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdeaCategory extends Model
{
    use HasFactory;

    protected $table = 'idea_category';
    protected $primaryKey = 'ideaCatID';

    protected $fillable = [
        'ideaID',
        'categoryID'
    ];

    public function idea()
    {
        return $this->belongsTo(Idea::class, 'ideaID', 'ideaID');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'categoryID', 'categoryID');
    }
}