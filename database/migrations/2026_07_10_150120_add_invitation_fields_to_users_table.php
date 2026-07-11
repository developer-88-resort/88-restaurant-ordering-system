<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
            $table->string('invitation_token')->nullable()->after('password');
            $table->timestamp('invitation_expires_at')->nullable()->after('invitation_token');
            $table->foreignId('invited_by')->nullable()->after('invitation_expires_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invited_by');
            $table->dropColumn(['invitation_token', 'invitation_expires_at']);
            $table->string('password')->nullable(false)->change();
        });
    }
};
