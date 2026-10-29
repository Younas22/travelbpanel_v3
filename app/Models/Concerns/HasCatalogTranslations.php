<?php

namespace App\Models\Concerns;

use App\Models\CatalogTranslation;

/** Per-language name/description stored in catalog_translations. */
trait HasCatalogTranslations
{
    public function translations()
    {
        return $this->morphMany(CatalogTranslation::class, 'translatable');
    }

    public function translated(string $field = 'name', ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $row = $this->relationLoaded('translations')
            ? $this->translations->firstWhere('locale', $locale)
            : $this->translations()->where('locale', $locale)->first();

        return ($row && filled($row->{$field})) ? $row->{$field} : ($this->{$field} ?? null);
    }
}
