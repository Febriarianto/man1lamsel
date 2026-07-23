<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class UpgradeV15Seeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'theme_preset', 'value' => 'identity-card', 'group' => 'theme', 'type' => 'select'],
            ['key' => 'theme_primary', 'value' => '#0877C9', 'group' => 'theme', 'type' => 'color'],
            ['key' => 'theme_primary_dark', 'value' => '#045A9D', 'group' => 'theme', 'type' => 'color'],
            ['key' => 'theme_accent', 'value' => '#F4CD00', 'group' => 'theme', 'type' => 'color'],
            ['key' => 'theme_background', 'value' => '#FFFFFF', 'group' => 'theme', 'type' => 'color'],
            ['key' => 'theme_surface', 'value' => '#F5F7FA', 'group' => 'theme', 'type' => 'color'],
            ['key' => 'theme_text', 'value' => '#171717', 'group' => 'theme', 'type' => 'color'],
            ['key' => 'theme_muted', 'value' => '#667085', 'group' => 'theme', 'type' => 'color'],
            ['key' => 'theme_border', 'value' => '#E3E8EF', 'group' => 'theme', 'type' => 'color'],
            ['key' => 'theme_white_ratio', 'value' => '75', 'group' => 'theme', 'type' => 'number'],
            ['key' => 'theme_primary_ratio', 'value' => '18', 'group' => 'theme', 'type' => 'number'],
            ['key' => 'theme_accent_ratio', 'value' => '5', 'group' => 'theme', 'type' => 'number'],
            ['key' => 'theme_neutral_ratio', 'value' => '2', 'group' => 'theme', 'type' => 'number'],
            ['key' => 'theme_pattern_enabled', 'value' => '1', 'group' => 'theme', 'type' => 'boolean'],
            ['key' => 'theme_apply_admin', 'value' => '1', 'group' => 'theme', 'type' => 'boolean'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
