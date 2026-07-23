<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'author_id', 'author_name', 'title', 'meta_title', 'slug', 'category',
        'excerpt', 'meta_description', 'meta_keywords', 'content', 'image',
        'status', 'featured', 'views', 'published_at',
    ];

    protected function casts(): array
    {
        return ['featured' => 'boolean', 'published_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post): void {
            $post->slug = $post->slug ?: Str::slug($post->title);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function getAuthorDisplayNameAttribute(): string
    {
        return $this->author_name ?: $this->author?->name ?: 'Administrator';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }
}
