<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Locale extends Model
{
    use HasFactory;

    protected $table = 'locales';

    protected $fillable = [
        'name',
        'abbreviation',
        'flag',
        'status'
    ];

    public function getStatusAttribute($value)
    {
        return $value ? 'enable' : 'disable';
    }
}
