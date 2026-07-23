<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpgradeV14Seeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->whereNull('auth_provider')->update(['auth_provider' => 'local']);
        DB::table('users')->whereNull('active')->update(['active' => true]);
        DB::table('users')->whereNotIn('role', ['admin', 'author'])->update(['role' => 'author']);

        DB::table('posts')
            ->whereNull('author_name')
            ->orderBy('id')
            ->chunkById(100, function ($posts): void {
                $names = DB::table('users')
                    ->whereIn('id', $posts->pluck('author_id')->filter()->unique())
                    ->pluck('name', 'id');

                foreach ($posts as $post) {
                    DB::table('posts')->where('id', $post->id)->update([
                        'author_name' => $names[$post->author_id] ?? 'Administrator',
                    ]);
                }
            });
    }
}
