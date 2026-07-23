<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Gallery extends Model
{
    protected $fillable = ['title','slug','type','image','video_url','description','published_at','active'];
    protected function casts(): array { return ['published_at'=>'datetime','active'=>'boolean']; }
    protected static function booted(): void
    {
        static::saving(function (Gallery $gallery) { $gallery->slug = $gallery->slug ?: Str::slug($gallery->title); });
    }
    public function getRouteKeyName(): string { return 'slug'; }
}
