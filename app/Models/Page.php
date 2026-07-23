<?php
namespace App\Models;
use App\Models\Concerns\HasUnderscoreSlug;
use Illuminate\Database\Eloquent\Model;
class Page extends Model
{
    use HasUnderscoreSlug;

    protected $fillable = ['title','meta_title','slug','excerpt','meta_description','meta_keywords','content','image','status','published_at'];
    protected function casts(): array { return ['published_at' => 'datetime']; }
    public function getRouteKeyName(): string { return 'slug'; }
    public function scopePublished($query) { return $query->where('status','published')->where(fn($q) => $q->whereNull('published_at')->orWhere('published_at','<=',now())); }
}
