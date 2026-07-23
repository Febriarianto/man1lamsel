<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('auth_provider', 40)->default('local')->after('role')->index();
            $table->string('provider_id')->nullable()->after('auth_provider');
            $table->string('nip', 50)->nullable()->after('provider_id')->index();
            $table->string('unit_name')->nullable()->after('nip');
            $table->string('avatar')->nullable()->after('unit_name');
            $table->boolean('active')->default(true)->after('avatar')->index();
            $table->timestamp('last_login_at')->nullable()->after('active');
            $table->unique(['auth_provider', 'provider_id'], 'users_provider_identity_unique');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->string('author_name')->nullable()->after('author_id');
        });

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

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('author_name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_provider_identity_unique');
            $table->dropColumn(['auth_provider', 'provider_id', 'nip', 'unit_name', 'avatar', 'active', 'last_login_at']);
        });
    }
};
