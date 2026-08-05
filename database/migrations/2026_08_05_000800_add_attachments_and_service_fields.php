<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->string('attachment')->nullable()->after('image');
            $table->string('attachment_name')->nullable()->after('attachment');
        });

        Schema::table('links', function (Blueprint $table): void {
            $table->string('description', 160)->nullable()->after('name');
            $table->boolean('new_tab')->default(true)->after('active');
        });

        $descriptions = [
            'Kementerian Agama RI' => 'Portal Kementerian Agama',
            'EMIS Madrasah' => 'Data Pendidikan Madrasah',
            'SIMPATIKA' => 'Layanan GTK Madrasah',
            'Kanwil Kemenag Lampung' => 'Portal Kemenag Lampung',
            'Rapor Digital Madrasah' => 'Rapor Digital',
            'E-Kinerja BKN' => 'Kinerja ASN',
        ];
        foreach ($descriptions as $name => $description) {
            DB::table('links')->where('name', $name)->whereNull('description')->update(['description' => $description]);
        }

        $informationMenuId = DB::table('menus')
            ->whereNull('parent_id')
            ->where('title', 'Informasi')
            ->value('id');

        if ($informationMenuId && ! DB::table('menus')->where('url', '/informasi')->exists()) {
            DB::table('menus')->insert([
                'parent_id' => $informationMenuId,
                'title' => 'Dokumen & Unduhan',
                'url' => '/informasi',
                'icon' => 'bi-file-earmark-arrow-down',
                'target' => '_self',
                'active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('menus')->where('url', '/informasi')->where('title', 'Dokumen & Unduhan')->delete();

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn(['attachment', 'attachment_name']);
        });

        Schema::table('links', function (Blueprint $table): void {
            $table->dropColumn(['description', 'new_tab']);
        });
    }
};
