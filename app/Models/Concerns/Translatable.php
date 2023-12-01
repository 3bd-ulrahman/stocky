<?php

namespace App\Models\Concerns;

use Astrotomic\Translatable\Translatable as BaseTranslatable;
use Illuminate\Support\Facades\DB;

trait Translatable
{
    use BaseTranslatable;

    protected function saveTranslations(): bool
    {
        $saved = true;

        if (! $this->relationLoaded('translations')) {
            return $saved;
        }

        $translations = [];
        foreach ($this->translations as $translation) {
            array_push($translations, $translation->attributes + [$this->getTranslationRelationKey() => $this->getKey()]);
        }

        $translation->setConnection($this->getConnectionName())->upsert($translations, 'id');

        return $saved;
    }
}
