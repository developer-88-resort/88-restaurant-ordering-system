<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `code` is only ever used to build the QR SVG download filename
 * (SpaceController::qrCode()) — nothing looks spaces up by it. Making it
 * globally unique was a mistake: it silently blocked the app's own naming
 * convention of reusing plain names like "Table 1" across different Areas
 * (Cottages, Dining Area, Rooms, ...), since `code` defaults to `name` on
 * create.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropUnique(['code']);
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->unique('code');
        });
    }
};
