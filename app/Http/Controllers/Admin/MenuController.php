<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::query()->whereNull('parent_id')->with('childrenRecursive')
            ->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.menus.index', compact('menus'));
    }

    public function create()
    {
        return view('admin.menus.form', [
            'menu' => new Menu,
            'parentOptions' => $this->parentOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['active'] = $request->boolean('active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        Menu::create($data);

        return redirect()->route('admin.menus.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu)
    {
        return view('admin.menus.form', [
            'menu' => $menu,
            'parentOptions' => $this->parentOptions($menu),
        ]);
    }

    public function update(Request $request, Menu $menu)
    {
        $data = $this->validated($request, $menu);
        $data['active'] = $request->boolean('active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        if (($data['parent_id'] ?? null) && in_array((int) $data['parent_id'], array_merge([$menu->id], $menu->descendantIds()), true)) {
            return back()->withInput()->withErrors(['parent_id' => 'Menu induk tidak boleh berasal dari menu itu sendiri atau turunannya.']);
        }

        $menu->update($data);

        return redirect()->route('admin.menus.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu)
    {
        $menu->children()->update(['parent_id' => $menu->parent_id]);
        $menu->delete();

        return back()->with('success', 'Menu berhasil dihapus. Submenu dipindahkan satu tingkat ke atas.');
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:menus,id'],
            'items.*.children' => ['sometimes', 'array'],
        ]);

        $seen = [];

        DB::transaction(function () use ($data, &$seen): void {
            $save = function (array $items, ?int $parentId = null) use (&$save, &$seen): void {
                foreach ($items as $position => $item) {
                    $id = (int) $item['id'];
                    if (in_array($id, $seen, true)) {
                        continue;
                    }
                    $seen[] = $id;
                    Menu::query()->whereKey($id)->update([
                        'parent_id' => $parentId,
                        'sort_order' => $position + 1,
                    ]);
                    $save($item['children'] ?? [], $id);
                }
            };
            $save($data['items']);
        });

        return response()->json(['message' => 'Urutan menu berhasil disimpan.']);
    }

    private function validated(Request $request, ?Menu $menu = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'url' => ['nullable', 'string', 'max:500'],
            'parent_id' => ['nullable', 'integer', 'exists:menus,id'],
            'icon' => ['nullable', 'string', 'max:100'],
            'target' => ['required', Rule::in(['_self', '_blank'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);
    }

    private function parentOptions(?Menu $excluded = null): array
    {
        $excludedIds = $excluded ? array_merge([$excluded->id], $excluded->descendantIds()) : [];
        $roots = Menu::query()->whereNull('parent_id')->with('childrenRecursive')
            ->orderBy('sort_order')->orderBy('id')->get();
        $options = [];

        $walk = function ($items, int $depth = 0) use (&$walk, &$options, $excludedIds): void {
            foreach ($items as $item) {
                if (! in_array($item->id, $excludedIds, true)) {
                    $options[$item->id] = str_repeat('— ', $depth).$item->title;
                    $walk($item->childrenRecursive, $depth + 1);
                }
            }
        };
        $walk($roots);

        return $options;
    }
}
