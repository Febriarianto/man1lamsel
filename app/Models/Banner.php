<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Banner extends Model
{
    protected $fillable = ['title','subtitle','button_text','button_url','image','sort_order','active'];
    protected function casts(): array { return ['active'=>'boolean']; }
}
