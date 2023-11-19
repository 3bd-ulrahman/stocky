<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $table = "warehouses";

    protected $fillable = [
        'name',
        'mobile',
        'country',
        'city',
        'email',
        'zip',
    ];

    protected $dates = ['deleted_at'];

    public function assignedUsers()
    {
        return $this->belongsToMany('App\Models\User');
    }
}
