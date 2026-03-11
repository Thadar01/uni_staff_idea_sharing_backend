<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';

    protected $primaryKey = 'documentID';

    protected $fillable = [
        'docPath',
        'fileType',
        'fileSize',
        'isHidden',
        'ideaID'
       
    ];

    // Relationship: Document belongs to Idea
    public function idea()
    {
        return $this->belongsTo(Idea::class, 'ideaID', 'ideaID');
    }
}