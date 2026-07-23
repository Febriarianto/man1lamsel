<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Page extends Model
{
    protected $fillable = ['title','meta_title','slug','excerpt','meta_description','meta_keywords','content','image','status','published_at'];
    protected function casts(): array { return ['published_at' => 'datetime']; }
    protected static function booted(): void
    {
        static::saving(function (Page $page) { $page->slug = $page->slug ?: Str::slug($page->title); });
    }
    public function getRouteKeyName(): string { return 'slug'; }
    public function scopePublished($query) { return $query->where('status','published')->where(fn($q) => $q->whereNull('published_at')->orWhere('published_at','<=',now())); }
}
