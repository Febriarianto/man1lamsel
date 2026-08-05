<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Link extends Model
{
    protected $fillable = ['name','description','url','icon','sort_order','active','new_tab'];
    protected function casts(): array { return ['active'=>'boolean', 'new_tab'=>'boolean']; }
}
