<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    use HandlesUploads;
    public function index(){ $banners=Banner::orderBy('sort_order')->paginate(15); return view('admin.banners.index',compact('banners')); }
    public function create(){ return view('admin.banners.form',['banner'=>new Banner]); }
    public function store(Request $request){ $data=$this->validated($request); $data['image']=$this->storeImage($request->file('image'),'banners'); $data['active']=$request->boolean('active'); Banner::create($data); return redirect()->route('admin.banners.index')->with('success','Banner berhasil ditambahkan.'); }
    public function edit(Banner $banner){ return view('admin.banners.form',compact('banner')); }
    public function update(Request $request,Banner $banner){ $data=$this->validated($request); $data['image']=$this->storeImage($request->file('image'),'banners',$banner->image); $data['active']=$request->boolean('active'); $banner->update($data); return redirect()->route('admin.banners.index')->with('success','Banner berhasil diperbarui.'); }
    public function destroy(Banner $banner){ if($banner->image && !str_starts_with($banner->image,'demo/')) Storage::disk('public')->delete($banner->image); $banner->delete(); return back()->with('success','Banner berhasil dihapus.'); }
    private function validated(Request $request): array { return $request->validate(['title'=>['required','string','max:255'],'subtitle'=>['nullable','string','max:500'],'button_text'=>['nullable','string','max:80'],'button_url'=>['nullable','string','max:500'],'image'=>['nullable','image','max:4096'],'sort_order'=>['required','integer','min:0']]); }
}
