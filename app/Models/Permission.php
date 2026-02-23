<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $table = 'permissions';

    protected $primaryKey = 'permissionID';

    protected $fillable = [
        'permission'
    ];


    public function roles()
{
    return $this->belongsToMany(
        Role::class,
        'role_permissions',
        'permissionID',
        'roleID'
    );
}
}
