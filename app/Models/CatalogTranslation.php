<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogTranslation extends Model
{
    protected $fillable = ['translatable_type', 'translatable_id', 'locale', 'name', 'description'];

    public function translatable()
    {
        return $this->morphTo();
    }
}
