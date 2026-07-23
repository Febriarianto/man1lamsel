<?php
namespace App\Models;
use App\Models\Concerns\HasUnderscoreSlug;
use Illuminate\Database\Eloquent\Model;
class Staff extends Model
{
    use HasUnderscoreSlug;

    protected $table = 'staff';
    protected string $slugSourceColumn = 'name';
    protected $fillable = ['name','slug','position','subject','type','photo','bio','sort_order','active'];
    protected function casts(): array { return ['active'=>'boolean']; }
    public function getRouteKeyName(): string { return 'slug'; }
}
