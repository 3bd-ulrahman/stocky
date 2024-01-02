<?php

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandTranslation extends Model
{
    use HasFactory;

    protected $table = 'brand_translations';

    protected $fillable = ['locale', 'name'];

    public $timestamps = false;
}
