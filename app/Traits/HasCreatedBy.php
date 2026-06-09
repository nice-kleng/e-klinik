<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasCreatedBy
{
    protected static function bootHasCreatedBy(): void
    {
        static::creating(function (Model $model) {
            if (auth()->check() && !$model->isDirty('created_by')) {
                $model->created_by = auth()->id();
            }
        });
    }
}
