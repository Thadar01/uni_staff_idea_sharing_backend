<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClosureSetting extends Model
{
    use HasFactory;

    protected $table = 'closure_setting';

    protected $primaryKey = 'settingID';

    protected $fillable = [
        'title',
        'closureDate',
        'finalclosureDate',
        'academicYear',
        'status'
    ];

    public function ideas()
    {
        return $this->hasMany(Idea::class, 'settingID', 'settingID');
    }
}