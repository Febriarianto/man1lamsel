<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Link extends Model
{
    protected $fillable = ['name','url','icon','sort_order','active'];
    protected function casts(): array { return ['active'=>'boolean']; }
}
