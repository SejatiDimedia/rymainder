<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->json('channels'); // ['email', 'whatsapp', 'telegram']
            $table->string('target_type')->default('all_active'); // all_active, overdue, selected
            $table->string('schedule_type')->default('daily'); // daily, multiple_daily, interval_hours, weekly, once
            $table->json('schedule_times')->nullable(); // ["08:00", "12:00", "17:00"]
            $table->unsignedInteger('interval_hours')->nullable(); // 2, 4, etc.
            $table->unsignedTinyInteger('schedule_day')->nullable(); // 1 = Monday to 7 = Sunday
            $table->dateTime('scheduled_at')->nullable(); // For once schedule
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_run_at')->nullable();
            $table->dateTime('next_run_at')->nullable()->index();
            $table->unsignedInteger('total_sent_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_reminders');
    }
};
