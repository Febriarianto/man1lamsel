<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = ['parent_id', 'title', 'url', 'icon', 'target', 'sort_order', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    public function getResolvedUrlAttribute(): string
    {
        $url = trim((string) $this->url);

        if ($url === '') {
            return '#';
        }

        if (str_starts_with($url, '#') || str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:') || filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        return url('/'.ltrim($url, '/'));
    }

    public function descendantIds(): array
    {
        $ids = [];
        $this->loadMissing('childrenRecursive');

        $walk = function ($children) use (&$walk, &$ids): void {
            foreach ($children as $child) {
                $ids[] = $child->id;
                $walk($child->childrenRecursive);
            }
        };

        $walk($this->childrenRecursive);

        return $ids;
    }
}
