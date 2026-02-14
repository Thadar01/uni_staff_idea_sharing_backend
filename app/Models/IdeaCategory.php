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

    // Relationship: belongs to Idea
    public function idea()
    {
        return $this->belongsTo(Idea::class, 'ideaID', 'ideaID');
    }

    // Relationship: belongs to Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'categoryID', 'categoryID');
    }
}