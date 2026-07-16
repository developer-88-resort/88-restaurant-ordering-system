<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops two fully superseded pieces of schema:
 *
 * - `tables` (+ `orders.table_id`): the pre-Areas/Spaces location system.
 *   Every current order-creation path uses area_id/space_category_id/
 *   space_id instead; table_id has been nullable and unused since the
 *   Areas/Spaces migration.
 * - `audit_logs`: the custom audit system replaced by
 *   spatie/laravel-activitylog (`activity_log`). Nothing writes to it
 *   anymore.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('table_id');
        });

        Schema::dropIfExists('tables');
        Schema::dropIfExists('audit_logs');
    }

    public function down(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('table_number')->unique();
            $table->string('status')->default('available');
            $table->string('qr_token')->unique();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('table_id')->nullable()->after('id')->constrained('tables')->nullOnDelete();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->string('description');
            $table->json('changes')->nullable();
            $table->timestamps();
        });
    }
};
