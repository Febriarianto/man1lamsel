<?php

namespace App\Http\Controllers;

use App\Models\Staff;

class StaffController extends Controller
{
    public function teachers() { return $this->index('guru'); }
    public function employees() { return $this->index('pegawai'); }

    public function index(string $type = 'guru')
    {
        abort_unless(in_array($type, ['guru', 'pegawai'], true), 404);
        $staff = Staff::query()->where('active', true)->where('type', $type)->orderBy('sort_order')->orderBy('name')->paginate(16);
        return view('staff.index', compact('staff', 'type'));
    }
}
