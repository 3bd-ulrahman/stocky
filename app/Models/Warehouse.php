<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    // Relationships
    public function assignedUsers()
    {
        return $this->belongsToMany('App\Models\User');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_warehouse');
    }
}
