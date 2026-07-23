<?php
namespace App\Models;
use App\Models\Concerns\HasUnderscoreSlug;
use Illuminate\Database\Eloquent\Model;
class Event extends Model
{
    use HasUnderscoreSlug;

    protected $fillable = ['title','slug','starts_at','ends_at','location','description','image','active'];
    protected function casts(): array { return ['starts_at'=>'datetime','ends_at'=>'datetime','active'=>'boolean']; }
    public function getRouteKeyName(): string { return 'slug'; }
}
