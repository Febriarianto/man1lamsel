<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUnderscoreSlug
{
    public static function makeSlug(?string $value): string
    {
        return Str::slug((string) $value, '_');
    }

    protected static function bootHasUnderscoreSlug(): void
    {
        static::saving(function ($model): void {
            $sourceColumn = property_exists($model, 'slugSourceColumn')
                ? $model->slugSourceColumn
                : 'title';

            $source = $model->getAttribute('slug') ?: $model->getAttribute($sourceColumn);
            $model->setAttribute('slug', static::makeSlug($source));
        });
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        $field ??= $this->getRouteKeyName();
        $value = (string) $value;
        $alternatives = array_values(array_unique([
            $value,
            static::makeSlug($value),
            str_replace('-', '_', $value),
            str_replace('_', '-', $value),
        ]));

        return $query->where(function ($bindingQuery) use ($field, $alternatives): void {
            foreach ($alternatives as $index => $alternative) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $bindingQuery->{$method}($field, $alternative);
            }
        });
    }
}
