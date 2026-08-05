<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with('staff')
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($sub) => $sub
                ->where('name', 'like', '%'.$request->q.'%')
                ->orWhere('email', 'like', '%'.$request->q.'%')
                ->orWhere('nip', 'like', '%'.$request->q.'%')
                ->orWhere('unit_name', 'like', '%'.$request->q.'%')
                ->orWhereHas('staff', fn ($staff) => $staff->where('name', 'like', '%'.$request->q.'%'))))
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->role))
            ->when($request->filled('provider'), fn ($query) => $query->where('auth_provider', $request->provider))
            ->latest('last_login_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create', [
            'staffOptions' => $this->availableStaff(),
        ]);
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user->load('staff'),
            'staffOptions' => $this->availableStaff($user),
        ]);
    }

    public function store(Request $request)
    {
        $this->normalizeNipInput($request);
        $data = $request->validate([
            'staff_id' => [
                'nullable',
                Rule::exists('staff', 'id')->where('active', true),
                Rule::unique('users', 'staff_id'),
            ],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'nip' => ['nullable', 'string', 'max:50', Rule::unique('users', 'nip')],
            'unit_name' => ['nullable', 'string', 'max:190'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'active' => ['nullable', 'boolean'],
        ]);

        $staff = $this->validateStaffIdentity($data);

        DB::transaction(function () use ($data, $request, $staff) {
            $nip = $this->resolvedNip($data, $staff);
            $user = User::create([
                'staff_id' => $staff?->id,
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'nip' => $nip,
                'unit_name' => $data['unit_name'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => 'author',
                'auth_provider' => 'local',
                'active' => $request->boolean('active'),
            ]);

            if ($staff && blank($staff->nip) && filled($nip)) {
                $staff->update(['nip' => $nip]);
            }
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun penulis manual berhasil dibuat dan sudah dapat digunakan untuk login.');
    }

    public function update(Request $request, User $user)
    {
        $this->normalizeNipInput($request);
        $data = $request->validate([
            'staff_id' => [
                'nullable',
                Rule::exists('staff', 'id')->where('active', true),
                Rule::unique('users', 'staff_id')->ignore($user->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'nip' => ['nullable', 'string', 'max:50', Rule::unique('users', 'nip')->ignore($user->id)],
            'unit_name' => ['nullable', 'string', 'max:190'],
            'role' => ['required', Rule::in(['admin', 'author'])],
            'active' => ['nullable', 'boolean'],
            'password' => $user->allowsManualLogin()
                ? ['nullable', 'confirmed', Password::min(8)]
                : ['prohibited'],
        ]);

        $staff = $this->validateStaffIdentity($data);
        $data['active'] = $request->boolean('active');
        $data['email'] = strtolower($data['email']);
        $data['staff_id'] = $staff?->id;
        $data['nip'] = $this->resolvedNip($data, $staff);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        if ($request->user()->is($user) && ($data['role'] !== 'admin' || ! $data['active'])) {
            return back()->withInput()
                ->with('error', 'Anda tidak dapat menonaktifkan atau menurunkan role akun administrator yang sedang digunakan.');
        }

        DB::transaction(function () use ($user, $data, $staff) {
            $user->update($data);
            if ($staff && blank($staff->nip) && filled($data['nip'])) {
                $staff->update(['nip' => $data['nip']]);
            }
        });

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Akun administrator tidak dapat dihapus dari menu penulis.');
        }

        if ($request->user()->is($user)) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }

        DB::transaction(function () use ($user): void {
            $user->posts()
                ->where(function ($query): void {
                    $query->whereNull('author_name')->orWhere('author_name', '');
                })
                ->update(['author_name' => $user->name]);

            $user->delete();
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun penulis berhasil dihapus. Artikel yang pernah ditulis tetap tersimpan.');
    }

    private function availableStaff(?User $user = null)
    {
        return Staff::query()
            ->where('active', true)
            ->where(function ($query) use ($user) {
                $query->whereDoesntHave('user');
                if ($user) {
                    $query->orWhereHas('user', fn ($linked) => $linked->whereKey($user->id));
                }
            })
            ->orderBy('name')
            ->get();
    }

    private function normalizeNipInput(Request $request): void
    {
        $nip = preg_replace('/\D+/', '', (string) $request->input('nip')) ?: null;
        $request->merge(['nip' => $nip]);
    }

    private function validateStaffIdentity(array $data): ?Staff
    {
        if (blank($data['staff_id'] ?? null)) {
            return null;
        }

        $staff = Staff::query()->findOrFail($data['staff_id']);
        $requestedNip = $data['nip'] ?? null;
        if (filled($staff->nip) && filled($requestedNip) && $staff->nip !== $requestedNip) {
            throw ValidationException::withMessages([
                'nip' => 'NIP akun harus sama dengan NIP pada data GTK yang dipilih.',
            ]);
        }

        if (blank($staff->nip) && filled($requestedNip)) {
            $duplicate = Staff::query()
                ->where('nip', $requestedNip)
                ->whereKeyNot($staff->id)
                ->exists();
            if ($duplicate) {
                throw ValidationException::withMessages([
                    'nip' => 'NIP tersebut sudah digunakan oleh data GTK lain.',
                ]);
            }
        }

        return $staff;
    }

    private function resolvedNip(array $data, ?Staff $staff): ?string
    {
        return $staff?->nip ?: ($data['nip'] ?? null);
    }
}
