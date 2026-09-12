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
        Schema::table('reminder_settings', function (Blueprint $table) {
            $table->string('schedule_frequency')->default('daily')->after('days_before_due'); // daily, weekly
            $table->unsignedTinyInteger('schedule_day')->nullable()->after('schedule_frequency'); // 1 = Monday ... 7 = Sunday
            $table->string('dispatch_time', 5)->default('07:00')->after('schedule_day'); // HH:MM (e.g. 07:00, 12:00)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reminder_settings', function (Blueprint $table) {
            $table->dropColumn(['schedule_frequency', 'schedule_day', 'dispatch_time']);
        });
    }
};
