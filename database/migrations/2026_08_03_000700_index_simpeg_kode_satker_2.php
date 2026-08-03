<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('simpeg_employees', function (Blueprint $table): void {
            $table->index('kode_satker_2');
        });
    }

    public function down(): void
    {
        Schema::table('simpeg_employees', function (Blueprint $table): void {
            $table->dropIndex(['kode_satker_2']);
        });
    }
};
