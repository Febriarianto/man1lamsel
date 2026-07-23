<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Staff extends Model
{
    protected $table = 'staff';
    protected $fillable = ['name','slug','position','subject','type','photo','bio','sort_order','active'];
    protected function casts(): array { return ['active'=>'boolean']; }
    protected static function booted(): void
    {
        static::saving(function (Staff $staff) { $staff->slug = $staff->slug ?: Str::slug($staff->name); });
    }
    public function getRouteKeyName(): string { return 'slug'; }
}
