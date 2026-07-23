<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Event extends Model
{
    protected $fillable = ['title','slug','starts_at','ends_at','location','description','image','active'];
    protected function casts(): array { return ['starts_at'=>'datetime','ends_at'=>'datetime','active'=>'boolean']; }
    protected static function booted(): void
    {
        static::saving(function (Event $event) { $event->slug = $event->slug ?: Str::slug($event->title); });
    }
    public function getRouteKeyName(): string { return 'slug'; }
}
