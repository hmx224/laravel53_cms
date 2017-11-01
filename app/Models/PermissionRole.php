<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionRole extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'permission_id',
        'role_id'
    ];

    protected $table = 'permission_role';

}