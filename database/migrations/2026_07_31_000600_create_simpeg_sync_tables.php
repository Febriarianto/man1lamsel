<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simpeg_employees', function (Blueprint $table) {
            $table->id();
            $table->string('identity_nip', 50)->unique();
            $table->string('nip', 50)->nullable()->index();
            $table->string('nip_baru', 50)->nullable()->index();
            $table->string('nama')->nullable();
            $table->string('nama_lengkap')->nullable();
            $table->string('agama', 50)->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin', 30)->nullable();
            $table->string('pendidikan')->nullable();
            $table->string('kode_level_jabatan', 50)->nullable();
            $table->string('level_jabatan')->nullable();
            $table->string('pangkat')->nullable();
            $table->string('gol_ruang', 50)->nullable();
            $table->date('tmt_cpns')->nullable();
            $table->date('tmt_pangkat')->nullable();
            $table->unsignedSmallInteger('mk_tahun')->nullable();
            $table->unsignedSmallInteger('mk_bulan')->nullable();
            $table->string('tipe_jabatan', 100)->nullable();
            $table->string('kode_jabatan', 100)->nullable();
            $table->text('tampil_jabatan')->nullable();
            $table->date('tmt_jabatan')->nullable();
            $table->string('kode_satuan_kerja', 50)->index();
            $table->string('satker_1')->nullable();
            $table->string('kode_satker_2', 50)->nullable();
            $table->string('satker_2')->nullable();
            $table->string('kode_satker_3', 50)->nullable();
            $table->string('satker_3')->nullable();
            $table->string('kode_satker_4', 50)->nullable();
            $table->string('satker_4')->nullable();
            $table->string('kode_satker_5', 50)->nullable();
            $table->string('satker_5')->nullable();
            $table->string('status_kawin', 50)->nullable();
            $table->text('alamat_1')->nullable();
            $table->text('alamat_2')->nullable();
            $table->string('telepon', 100)->nullable();
            $table->string('kab_kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kode_pos', 20)->nullable();
            $table->string('kode_lokasi', 50)->nullable();
            $table->string('kode_pangkat', 50)->nullable();
            $table->string('no_hp', 100)->nullable();
            $table->string('email')->nullable();
            $table->date('tmt_pangkat_yad')->nullable();
            $table->date('tmt_kgb_yad')->nullable();
            $table->date('tmt_pensiun')->nullable();
            $table->string('kode_kua', 50)->nullable();
            $table->string('nsm', 50)->nullable();
            $table->string('npsn', 50)->nullable();
            $table->string('status_pegawai', 100)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->json('source_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('simpeg_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('satker_code', 50);
            $table->string('status', 20)->default('running')->index();
            $table->unsignedInteger('total_reported')->default(0);
            $table->unsignedInteger('fetched')->default(0);
            $table->unsignedInteger('matched')->default(0);
            $table->unsignedInteger('inserted')->default(0);
            $table->unsignedInteger('updated')->default(0);
            $table->unsignedInteger('skipped')->default(0);
            $table->unsignedInteger('staff_created')->default(0);
            $table->unsignedInteger('staff_updated')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simpeg_sync_logs');
        Schema::dropIfExists('simpeg_employees');
    }
};
