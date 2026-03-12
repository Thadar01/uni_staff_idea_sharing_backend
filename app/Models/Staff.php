<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Staff extends Authenticatable implements JWTSubject
{
    protected $table = 'staffs';
    protected $primaryKey = 'staffID';
    public $timestamps = false; // because you are using createdDateTime

    protected $fillable = [
        'staffName',
        'staffPhNo',
        'staffEmail',
        'staffPassword',
        'staffDOB',
        'staffAddress',
        'staffProfile',
        'termsAccepted',
        'termsAcceptedDate',
        'createdDateTime',
        'last_login_at',
        'account_status',
        'departmentID',
        'roleID'
    ];

    protected $hidden = [
        'staffPassword'
    ];
    protected $casts = [
    'termsAccepted' => 'boolean',
    'termsAcceptedDate' => 'datetime:Y-m-d H:i:s',
    'createdDateTime' => 'datetime:Y-m-d H:i:s',
    'last_login_at' => 'datetime:Y-m-d H:i:s',
];

    /**
     * Tell Laravel which column is password
     */
    public function getAuthPassword()
    {
        return $this->staffPassword;
    }

    /**
     * JWT: Get identifier
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * JWT: Custom claims (optional)
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Relationship: Department
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'departmentID', 'departmentID');
    }

    /**
     * Relationship: Role
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'roleID', 'roleID');
    }
}
