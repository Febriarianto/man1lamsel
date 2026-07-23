<?php

namespace App\Models;

use App\Models\Concerns\HasUnderscoreSlug;
use Illuminate\Database\Eloquent\Model;

class Infographic extends Model
{
    use HasUnderscoreSlug;

    protected $fillable = [
        'title', 'meta_title', 'slug', 'description', 'meta_description', 'meta_keywords',
        'image', 'source_name', 'source_url', 'published_at', 'featured', 'active',
        'sort_order', 'views',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'featured' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($query)
    {
        return $query->where('active', true)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }
}
