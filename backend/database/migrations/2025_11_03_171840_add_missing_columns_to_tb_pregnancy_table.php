<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_pregnancy', function (Blueprint $table) {
            $table->string('air_bersih')->nullable();
            $table->string('bantuan_sosial')->nullable();
            $table->string('fasilitas_rujukan')->nullable();
            $table->string('jamban_sehat')->nullable();
            $table->string('mendapat_ttd')->nullable();
            $table->string('mendapat_edukasi')->nullable();
            $table->string('rencana_tempat_melahirkan')->nullable();
            $table->string('rencana_asi_ekslusif')->nullable();
            $table->string('rencana_tinggal_setelah_melahirkan')->nullable();
            $table->string('rencana_kontrasepsi')->nullable();
            $table->string('riwayat_keguguran')->nullable();
            $table->string('terpapar_asap_rokok')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tb_pregnancy', function (Blueprint $table) {
            $table->dropColumn([
                'air_bersih',
                'bantuan_sosial',
                'fasilitas_rujukan',
                'jamban_sehat',
                'mendapat_ttd',
                'mendapat_edukasi',
                'rencana_tempat_melahirkan',
                'rencana_asi_ekslusif',
                'rencana_tinggal_setelah_melahirkan',
                'rencana_kontrasepsi',
                'riwayat_keguguran',
                'terpapar_asap_rokok',
            ]);
        });
    }
};
