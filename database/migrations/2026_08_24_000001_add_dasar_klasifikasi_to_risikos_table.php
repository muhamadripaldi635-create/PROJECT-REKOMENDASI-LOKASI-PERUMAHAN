<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('risikos', 'dasar_klasifikasi')) {
            Schema::table('risikos', function (Blueprint $table) {
                $table->text('dasar_klasifikasi')->nullable()->after('tingkat');
            });
        }

        DB::table('risikos')->whereNull('dasar_klasifikasi')->where('tipe', 'longsor')->update([
            'dasar_klasifikasi' => 'Klasifikasi berdasarkan interpretasi lokasi pada data GIS, terutama indikasi topografi atau kemiringan lahan, kondisi tanah/geologi, dan riwayat longsor bila tersedia. Penetapan ini perlu dikonfirmasi dengan survei lapangan atau data teknis resmi.',
        ]);
        DB::table('risikos')->whereNull('dasar_klasifikasi')->where('tipe', 'banjir')->update([
            'dasar_klasifikasi' => 'Klasifikasi berdasarkan interpretasi lokasi pada data GIS, terutama kedekatan dengan sungai atau saluran air, kondisi elevasi dan drainase, serta riwayat banjir bila tersedia. Penetapan ini perlu dikonfirmasi dengan survei lapangan atau data teknis resmi.',
        ]);
        DB::table('risikos')->whereNull('dasar_klasifikasi')->where('tipe', 'aman')->update([
            'dasar_klasifikasi' => 'Klasifikasi berdasarkan peninjauan data GIS lokasi. Tidak ditemukan risiko banjir atau longsor yang ditandai pada data yang tersedia.',
        ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('risikos', 'dasar_klasifikasi')) {
            Schema::table('risikos', function (Blueprint $table) {
                $table->dropColumn('dasar_klasifikasi');
            });
        }
    }
};
