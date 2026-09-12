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
        Schema::table('reminder_logs', function (Blueprint $table) {
            $table->foreignId('reminder_setting_id')->nullable()->change();
            $table->boolean('is_manual')->default(false)->after('channel');
            $table->text('custom_message')->nullable()->after('error_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reminder_logs', function (Blueprint $table) {
            $table->dropColumn(['is_manual', 'custom_message']);
            $table->foreignId('reminder_setting_id')->nullable(false)->change();
        });
    }
};
