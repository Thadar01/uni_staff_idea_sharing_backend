<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'roleID';

    protected $fillable = [
        'roleName'
    ];

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions',
            'roleID',
            'permissionID'
        );
    }
}