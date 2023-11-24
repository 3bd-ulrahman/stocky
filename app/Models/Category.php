<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use App\Models\Translations\CategoryTranslation;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model implements TranslatableContract
{
    use SoftDeletes, Translatable;

    protected $table = 'categories';

    protected $fillable = [
        'code',
    ];

    public $translatedAttributes = ['name'];

    public $translationModel = CategoryTranslation::class;

    public function insertWithTranslations()
    {

    }
}
