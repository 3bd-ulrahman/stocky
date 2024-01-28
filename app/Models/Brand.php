<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use App\Models\Translations\BrandTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model implements TranslatableContract
{
    use SoftDeletes, Translatable;

    protected $table = 'brands';

    protected $fillable = [
        'description',
        'image',
    ];

    public $translatedAttributes = ['name'];

    public $translationModel = BrandTranslation::class;

    protected $with = ['translations'];
}
