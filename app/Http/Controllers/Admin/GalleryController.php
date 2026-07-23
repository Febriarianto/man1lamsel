<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class GalleryController extends Controller
{
    use HandlesUploads;
    public function index(Request $request) { $galleries=Gallery::query()->when($request->filled('type'),fn($q)=>$q->where('type',$request->type))->latest()->paginate(15)->withQueryString(); return view('admin.galleries.index',compact('galleries')); }
    public function create() { return view('admin.galleries.form',['gallery'=>new Gallery]); }
    public function store(Request $request) { $data=$this->validated($request); $data['image']=$this->storeImage($request->file('image'),'galleries'); $data['active']=$request->boolean('active'); Gallery::create($data); return redirect()->route('admin.galleries.index')->with('success','Galeri berhasil ditambahkan.'); }
    public function edit(Gallery $gallery) { return view('admin.galleries.form',compact('gallery')); }
    public function update(Request $request, Gallery $gallery) { $data=$this->validated($request,$gallery); $data['image']=$this->storeImage($request->file('image'),'galleries',$gallery->image); $data['active']=$request->boolean('active'); $gallery->update($data); return redirect()->route('admin.galleries.index')->with('success','Galeri berhasil diperbarui.'); }
    public function destroy(Gallery $gallery) { if($gallery->image && !str_starts_with($gallery->image,'demo/')) Storage::disk('public')->delete($gallery->image); $gallery->delete(); return back()->with('success','Galeri berhasil dihapus.'); }
    private function validated(Request $request, ?Gallery $gallery=null): array
    {
        $request->merge(['slug' => Gallery::makeSlug($request->input('slug') ?: $request->input('title'))]);

        return $request->validate(['title'=>['required','string','max:255'],'slug'=>['required','string','max:255','regex:/^[a-z0-9]+(?:_[a-z0-9]+)*$/',Rule::unique('galleries','slug')->ignore($gallery?->id)],'type'=>['required',Rule::in(['photo','video'])],'image'=>['nullable','image','max:4096'],'video_url'=>['nullable','url','max:500'],'description'=>['nullable','string','max:2000'],'published_at'=>['nullable','date']]);
    }
}
