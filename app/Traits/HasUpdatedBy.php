<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasUpdatedBy
{
    protected static function bootHasUpdatedBy(): void
    {
        static::updating(function (Model $model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }
}
