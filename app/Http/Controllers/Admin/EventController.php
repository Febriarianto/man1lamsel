<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    use HandlesUploads;
    public function index(){ $events=Event::orderByDesc('starts_at')->paginate(15); return view('admin.events.index',compact('events')); }
    public function create(){ return view('admin.events.form',['event'=>new Event]); }
    public function store(Request $request){ $data=$this->validated($request); $data['image']=$this->storeImage($request->file('image'),'events'); $data['active']=$request->boolean('active'); Event::create($data); return redirect()->route('admin.events.index')->with('success','Agenda berhasil ditambahkan.'); }
    public function edit(Event $event){ return view('admin.events.form',compact('event')); }
    public function update(Request $request,Event $event){ $data=$this->validated($request,$event); $data['image']=$this->storeImage($request->file('image'),'events',$event->image); $data['active']=$request->boolean('active'); $event->update($data); return redirect()->route('admin.events.index')->with('success','Agenda berhasil diperbarui.'); }
    public function destroy(Event $event){ if($event->image && !str_starts_with($event->image,'demo/')) Storage::disk('public')->delete($event->image); $event->delete(); return back()->with('success','Agenda berhasil dihapus.'); }
    private function validated(Request $request, ?Event $event=null): array
    {
        $request->merge(['slug' => Event::makeSlug($request->input('slug') ?: $request->input('title'))]);

        return $request->validate(['title'=>['required','string','max:255'],'slug'=>['required','string','max:255','regex:/^[a-z0-9]+(?:_[a-z0-9]+)*$/',Rule::unique('events','slug')->ignore($event?->id)],'starts_at'=>['required','date'],'ends_at'=>['nullable','date','after_or_equal:starts_at'],'location'=>['nullable','string','max:255'],'description'=>['nullable','string','max:3000'],'image'=>['nullable','image','max:4096']]);
    }
}
