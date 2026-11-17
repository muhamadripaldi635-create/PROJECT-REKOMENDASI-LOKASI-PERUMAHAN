<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('perumahans')
            ->where('longitude', '>=', 10.0)
            ->where('longitude', '<=', 11.0)
            ->update([
                'longitude' => DB::raw('longitude * 10')
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('perumahans')
            ->where('longitude', '>=', 100.0)
            ->where('longitude', '<=', 110.0)
            ->update([
                'longitude' => DB::raw('longitude / 10')
            ]);
    }
};

