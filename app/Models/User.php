<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'firstname',
        'lastname',
        'username',
        'email',
        'password',
        'is_representative',
        'phone',
        'statut',
        'avatar',
        'role_id',
        'is_all_warehouses'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        // 'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role_id' => 'integer',
        'statut' => 'integer',
        'is_all_warehouses' => 'integer',
    ];

    // Relationships
    public function oauthAccessToken()
    {
        return $this->hasMany(OauthAccessToken::class, 'user_id', 'id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function assignRole(Role $role)
    {
        return $this->roles()->save($role);
    }

    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }
        return !!$role->intersect($this->roles)->count();
    }

    public function assignedWarehouses()
    {
        return $this->belongsToMany('App\Models\Warehouse');
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'user_id', 'id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'user_id', 'id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'user_id', 'id');
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class, 'user_id', 'id');
    }

    public function SaleReturns(): HasMany
    {
        return $this->hasMany(SaleReturn::class, 'user_id', 'id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'user_id', 'id');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(Adjustment::class, 'user_id', 'id');
    }

    // Accessorss & Mutators
    protected function password(): Attribute
    {
        return new Attribute(
            set: fn ($password) => Hash::needsRehash($password) && !is_null($password)
                ? bcrypt($password)
                : $password
        );
    }
}
