<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable as BaseTranslatable;
use Illuminate\Database\Eloquent\Builder;

trait Translatable
{
    use BaseTranslatable;

    /**
     * Insert a new record with translations into the database.
     *
     * @param  array  $values
     * @return bool
     */
    public static function insertWithTranslations(array $values)
    {
        $translations = [];

        $model = new static;

        foreach ($model->translatedAttributes as $attribute) {
            if (isset($values[$attribute]) && is_array($values[$attribute])) {
                foreach ($values[$attribute] as $locale => $translation) {
                    $translations[$locale][$attribute] = $translation;
                }
                unset($values[$attribute]);
            }
        }

        $result = static::query()->insert($values);

        if (!empty($translations)) {
            $modelTranslations = [];

            foreach ($translations as $locale => $translation) {
                foreach ($translation as $attribute => $value) {
                    $modelTranslations[] = [
                        'locale' => $locale,
                        'attribute' => $attribute,
                        'value' => $value,
                    ];
                }
            }

            $model->translations()->insert($modelTranslations);
        }

        return $result;
    }
}
