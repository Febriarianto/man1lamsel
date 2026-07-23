<?php
namespace App\Models;
use App\Models\Concerns\HasUnderscoreSlug;
use Illuminate\Database\Eloquent\Model;
class Gallery extends Model
{
    use HasUnderscoreSlug;

    protected $fillable = ['title','slug','type','image','video_url','description','published_at','active'];
    protected function casts(): array { return ['published_at'=>'datetime','active'=>'boolean']; }
    public function getRouteKeyName(): string { return 'slug'; }
}
