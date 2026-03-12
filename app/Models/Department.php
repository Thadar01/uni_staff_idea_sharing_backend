<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'departmentID';

    protected $fillable = [
        'departmentName'
    ];

    public function staffs()
    {
        return $this->hasMany(Staff::class, 'departmentID', 'departmentID');
    }

    public function qaCoordinator()
    {
        return $this->hasOne(Staff::class, 'departmentID', 'departmentID')
            ->whereHas('role', function ($query) {
                $query->where('roleName', 'QA Coordinator');
            });
    }
}