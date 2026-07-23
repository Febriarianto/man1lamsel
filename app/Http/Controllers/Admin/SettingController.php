<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{
    use HandlesUploads;

    private const THEME_PRESETS = [
        'identity-card' => [
            'theme_primary' => '#0877C9',
            'theme_primary_dark' => '#045A9D',
            'theme_accent' => '#F4CD00',
            'theme_background' => '#FFFFFF',
            'theme_surface' => '#F5F7FA',
            'theme_text' => '#171717',
            'theme_muted' => '#667085',
            'theme_border' => '#E3E8EF',
        ],
        'ocean' => [
            'theme_primary' => '#0369A1',
            'theme_primary_dark' => '#075985',
            'theme_accent' => '#38BDF8',
            'theme_background' => '#FFFFFF',
            'theme_surface' => '#F0F9FF',
            'theme_text' => '#0F172A',
            'theme_muted' => '#64748B',
            'theme_border' => '#D8EAF5',
        ],
        'emerald' => [
            'theme_primary' => '#087A55',
            'theme_primary_dark' => '#05583E',
            'theme_accent' => '#EAB308',
            'theme_background' => '#FFFFFF',
            'theme_surface' => '#F2F8F5',
            'theme_text' => '#17231E',
            'theme_muted' => '#65736C',
            'theme_border' => '#DFE8E3',
        ],
        'maroon' => [
            'theme_primary' => '#9F1239',
            'theme_primary_dark' => '#701A36',
            'theme_accent' => '#F59E0B',
            'theme_background' => '#FFFFFF',
            'theme_surface' => '#FFF7F8',
            'theme_text' => '#24151A',
            'theme_muted' => '#73636A',
            'theme_border' => '#F0DDE3',
        ],
    ];

    public function edit()
    {
        $order = ['general', 'branding', 'theme', 'seo', 'contact', 'social', 'services', 'statistics'];
        $settings = Setting::query()->orderBy('key')->get()->groupBy('group')
            ->sortBy(fn ($items, $group) => array_search($group, $order, true) === false ? 999 : array_search($group, $order, true));

        return view('admin.settings.edit', [
            'settings' => $settings,
            'themePresets' => self::THEME_PRESETS,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_logo' => ['nullable', 'image', 'max:4096'],
            'site_favicon' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,webp,svg', 'max:2048'],
            'seo_og_image' => ['nullable', 'image', 'max:4096'],
            'seo_default_title' => ['nullable', 'string', 'max:255'],
            'seo_default_description' => ['nullable', 'string', 'max:500'],
            'seo_default_keywords' => ['nullable', 'string', 'max:500'],
            'seo_title_separator' => ['nullable', 'string', 'max:10'],
            'seo_google_verification' => ['nullable', 'string', 'max:255'],
            'seo_bing_verification' => ['nullable', 'string', 'max:255'],
            'seo_analytics_id' => ['nullable', 'string', 'max:50'],
            'theme_preset' => ['nullable', 'in:identity-card,ocean,emerald,maroon,custom'],
            'theme_primary' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_primary_dark' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_accent' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_background' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_surface' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_text' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_muted' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_border' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_white_ratio' => ['nullable', 'integer', 'min:0', 'max:100'],
            'theme_primary_ratio' => ['nullable', 'integer', 'min:0', 'max:100'],
            'theme_accent_ratio' => ['nullable', 'integer', 'min:0', 'max:100'],
            'theme_neutral_ratio' => ['nullable', 'integer', 'min:0', 'max:100'],
        ], [
            'regex' => 'Format warna harus berupa kode HEX enam digit, misalnya #0877C9.',
        ]);

        if ($request->has(['theme_white_ratio', 'theme_primary_ratio', 'theme_accent_ratio', 'theme_neutral_ratio'])) {
            $ratioTotal = collect([
                $request->integer('theme_white_ratio'),
                $request->integer('theme_primary_ratio'),
                $request->integer('theme_accent_ratio'),
                $request->integer('theme_neutral_ratio'),
            ])->sum();

            if ($ratioTotal !== 100) {
                throw ValidationException::withMessages([
                    'theme_white_ratio' => 'Total komposisi warna harus tepat 100%. Saat ini '.$ratioTotal.'%.',
                ]);
            }
        }

        $preset = $request->string('theme_preset')->toString();
        if (isset(self::THEME_PRESETS[$preset])) {
            $request->merge(self::THEME_PRESETS[$preset]);
        }

        $settings = Setting::all()->keyBy('key');

        foreach ($settings as $key => $setting) {
            if ($setting->type === 'image') {
                if ($request->hasFile($key)) {
                    $setting->update([
                        'value' => $this->storeImage($request->file($key), 'settings', $setting->value),
                    ]);
                }
                continue;
            }

            if ($setting->type === 'boolean') {
                $setting->update(['value' => $request->boolean($key) ? '1' : '0']);
                continue;
            }

            if ($request->exists($key)) {
                $value = $request->input($key);
                if ($setting->type === 'color') {
                    $value = Setting::normalizeHex((string) $value);
                }
                $setting->update(['value' => $value]);
            }
        }

        return back()->with('success', 'Pengaturan situs, tema warna, branding, dan SEO berhasil disimpan.');
    }
}
