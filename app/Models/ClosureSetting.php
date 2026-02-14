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
        'closureDate',
        'finalclosureDate',
        'academicYear'
    ];
}