<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    private array $slugTables = [
        'posts',
        'pages',
        'staff',
        'galleries',
        'events',
        'infographics',
    ];

    public function up(): void
    {
        DB::transaction(function (): void {
            foreach ($this->slugTables as $table) {
                $this->convertTable($table, '_');
            }

            $this->convertProfileUrls('_');
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            foreach ($this->slugTables as $table) {
                $this->convertTable($table, '-');
            }

            $this->convertProfileUrls('-');
        });
    }

    private function convertTable(string $table, string $separator): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'slug')) {
            return;
        }

        $rows = DB::table($table)->select(['id', 'slug'])->orderBy('id')->get();
        $used = [];
        $converted = [];

        foreach ($rows as $row) {
            $base = Str::slug((string) $row->slug, $separator);
            if ($base === '') {
                $base = 'item'.$separator.$row->id;
            }

            $candidate = $base;
            $suffix = 2;
            while (isset($used[$candidate])) {
                $candidate = $base.$separator.$suffix;
                $suffix++;
            }

            $used[$candidate] = true;
            $converted[$row->id] = $candidate;
        }

        foreach ($rows as $row) {
            DB::table($table)->where('id', $row->id)->update([
                'slug' => '__slug_conversion_'.$table.'_'.$row->id.'__',
            ]);
        }

        foreach ($converted as $id => $slug) {
            DB::table($table)->where('id', $id)->update(['slug' => $slug]);
        }
    }

    private function convertProfileUrls(string $separator): void
    {
        foreach (['menus' => 'url', 'banners' => 'button_url'] as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::table($table)
                ->where($column, 'like', '/profil/%')
                ->select(['id', $column])
                ->orderBy('id')
                ->get()
                ->each(function ($row) use ($table, $column, $separator): void {
                    $prefix = '/profil/';
                    $slug = Str::after((string) $row->{$column}, $prefix);
                    DB::table($table)->where('id', $row->id)->update([
                        $column => $prefix.Str::slug($slug, $separator),
                    ]);
                });
        }
    }
};
