<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reminder_logs', function (Blueprint $table) {
            $table->foreignId('custom_reminder_id')
                ->nullable()
                ->after('reminder_setting_id')
                ->constrained('custom_reminders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reminder_logs', function (Blueprint $table) {
            $table->dropForeign(['custom_reminder_id']);
            $table->dropColumn('custom_reminder_id');
        });
    }
};
