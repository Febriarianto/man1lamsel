<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    use HandlesUploads;
    public function index(Request $request)
    {
        $staff = Staff::query()->when($request->filled('type'), fn($q) => $q->where('type',$request->type))->orderBy('sort_order')->paginate(20)->withQueryString();
        return view('admin.staff.index', compact('staff'));
    }
    public function create() { return view('admin.staff.form', ['staffMember'=>new Staff]); }
    public function store(Request $request)
    {
        $data=$this->validated($request); $data['photo']=$this->storeImage($request->file('photo'),'staff'); $data['active']=$request->boolean('active'); Staff::create($data);
        return redirect()->route('admin.staff.index')->with('success','Data GTK berhasil ditambahkan.');
    }
    public function edit(Staff $staff) { return view('admin.staff.form', ['staffMember'=>$staff]); }
    public function update(Request $request, Staff $staff)
    {
        $data=$this->validated($request,$staff); $data['photo']=$this->storeImage($request->file('photo'),'staff',$staff->photo); $data['active']=$request->boolean('active'); $staff->update($data);
        return redirect()->route('admin.staff.index')->with('success','Data GTK berhasil diperbarui.');
    }
    public function destroy(Staff $staff)
    {
        if($staff->photo && !str_starts_with($staff->photo,'demo/')) Storage::disk('public')->delete($staff->photo); $staff->delete(); return back()->with('success','Data berhasil dihapus.');
    }
    private function validated(Request $request, ?Staff $staff=null): array
    {
        return $request->validate([
            'name'=>['required','string','max:150'],'slug'=>['nullable','string','max:160',Rule::unique('staff','slug')->ignore($staff?->id)],
            'position'=>['required','string','max:150'],'subject'=>['nullable','string','max:150'],'type'=>['required',Rule::in(['kepala','guru','pegawai'])],
            'photo'=>['nullable','image','max:3072'],'bio'=>['nullable','string','max:3000'],'sort_order'=>['required','integer','min:0'],
        ]);
    }
}
