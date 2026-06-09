<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeSearch(Builder $query, ?string $keyword, array $fields = []): Builder
    {
        if (empty($keyword) || empty($fields)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($keyword, $fields) {
            foreach ($fields as $field) {
                $q->orWhere($field, 'like', "%{$keyword}%");
            }
        });
    }

    public function scopeFilterBy(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if ($value !== null && $value !== '' && in_array($field, $this->fillable ?? [])) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        return $query;
    }
}
