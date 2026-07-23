<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($sub) => $sub
                ->where('name', 'like', '%'.$request->q.'%')
                ->orWhere('email', 'like', '%'.$request->q.'%')
                ->orWhere('nip', 'like', '%'.$request->q.'%')
                ->orWhere('unit_name', 'like', '%'.$request->q.'%')))
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->role))
            ->when($request->filled('provider'), fn ($query) => $query->where('auth_provider', $request->provider))
            ->latest('last_login_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'nip' => ['nullable', 'string', 'max:50'],
            'unit_name' => ['nullable', 'string', 'max:190'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'active' => ['nullable', 'boolean'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'nip' => $data['nip'] ?? null,
            'unit_name' => $data['unit_name'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'author',
            'auth_provider' => 'local',
            'active' => $request->boolean('active'),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun penulis manual berhasil dibuat dan sudah dapat digunakan untuk login.');
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'nip' => ['nullable', 'string', 'max:50'],
            'unit_name' => ['nullable', 'string', 'max:190'],
            'role' => ['required', Rule::in(['admin', 'author'])],
            'active' => ['nullable', 'boolean'],
            'password' => $user->usesSso()
                ? ['prohibited']
                : ['nullable', 'confirmed', Password::min(8)],
        ];
        $data = $request->validate($rules);
        $data['active'] = $request->boolean('active');
        $data['email'] = strtolower($data['email']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        if ($request->user()->is($user) && ($data['role'] !== 'admin' || ! $data['active'])) {
            return back()->withInput()->with('error', 'Anda tidak dapat menonaktifkan atau menurunkan role akun administrator yang sedang digunakan.');
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }
}
