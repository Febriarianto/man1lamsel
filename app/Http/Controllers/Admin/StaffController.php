<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    use HandlesUploads;

    public function index(Request $request)
    {
        $staff = Staff::query()
            ->leftJoin('simpeg_employees as simpeg', function ($join): void {
                $join->on('staff.nip', '=', 'simpeg.identity_nip')
                    ->where('simpeg.kode_satker_2', '=', (string) config('simpeg.satker_2_code'));
            })
            ->select([
                'staff.*',
                'simpeg.id as simpeg_employee_id',
                'simpeg.status_pegawai as simpeg_status_pegawai',
                'simpeg.pangkat as simpeg_pangkat',
                'simpeg.gol_ruang as simpeg_gol_ruang',
                'simpeg.pendidikan as simpeg_pendidikan',
                'simpeg.email as simpeg_email',
                'simpeg.no_hp as simpeg_no_hp',
                'simpeg.tampil_jabatan as simpeg_jabatan',
                'simpeg.satker_2 as simpeg_satker_2',
                'simpeg.synced_at as simpeg_synced_at',
            ])
            ->with('user')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q')->trim().'%';
                $query->where(function ($search) use ($term): void {
                    $search->where('staff.name', 'like', $term)
                        ->orWhere('staff.nip', 'like', $term)
                        ->orWhere('staff.position', 'like', $term)
                        ->orWhere('staff.subject', 'like', $term)
                        ->orWhere('simpeg.nama_lengkap', 'like', $term)
                        ->orWhere('simpeg.tampil_jabatan', 'like', $term)
                        ->orWhere('simpeg.status_pegawai', 'like', $term)
                        ->orWhere('simpeg.pangkat', 'like', $term)
                        ->orWhere('simpeg.gol_ruang', 'like', $term)
                        ->orWhere('simpeg.pendidikan', 'like', $term)
                        ->orWhere('simpeg.email', 'like', $term)
                        ->orWhere('simpeg.no_hp', 'like', $term);
                });
            })
            ->when($request->filled('type'), fn ($query) => $query->where('staff.type', $request->type))
            ->orderBy('staff.sort_order')
            ->orderBy('staff.name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.staff.index', compact('staff'));
    }

    public function create()
    {
        return view('admin.staff.form', ['staffMember' => new Staff]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['photo'] = $this->storeImage($request->file('photo'), 'staff');
        $data['active'] = $request->boolean('active');
        Staff::create($data);

        return redirect()->route('admin.staff.index')->with('success', 'Data GTK berhasil ditambahkan.');
    }

    public function edit(Staff $staff)
    {
        return view('admin.staff.form', ['staffMember' => $staff->load('user')]);
    }

    public function update(Request $request, Staff $staff)
    {
        $data = $this->validated($request, $staff);
        $data['photo'] = $this->storeImage($request->file('photo'), 'staff', $staff->photo);
        $data['active'] = $request->boolean('active');

        DB::transaction(function () use ($staff, $data) {
            $staff->update($data);
            if ($staff->user) {
                $staff->user->update(['nip' => $staff->nip]);
            }
        });

        return redirect()->route('admin.staff.index')->with('success', 'Data GTK berhasil diperbarui.');
    }

    public function destroy(Staff $staff)
    {
        if ($staff->photo && ! str_starts_with($staff->photo, 'demo/')) {
            Storage::disk('public')->delete($staff->photo);
        }

        $staff->delete();

        return back()->with('success', 'Data berhasil dihapus.');
    }

    private function validated(Request $request, ?Staff $staff = null): array
    {
        $request->merge([
            'slug' => Staff::makeSlug($request->input('slug') ?: $request->input('name')),
            'nip' => preg_replace('/\D+/', '', (string) $request->input('nip')) ?: null,
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'nip' => ['nullable', 'string', 'max:50', Rule::unique('staff', 'nip')->ignore($staff?->id)],
            'slug' => [
                'required',
                'string',
                'max:160',
                'regex:/^[a-z0-9]+(?:_[a-z0-9]+)*$/',
                Rule::unique('staff', 'slug')->ignore($staff?->id),
            ],
            'position' => ['required', 'string', 'max:150'],
            'subject' => ['nullable', 'string', 'max:150'],
            'type' => ['required', Rule::in(['kepala', 'guru', 'pegawai'])],
            'photo' => ['nullable', 'image', 'max:3072'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);
    }
}
