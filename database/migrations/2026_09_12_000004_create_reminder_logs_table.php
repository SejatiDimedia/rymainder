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
        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsor_id')->constrained('sponsors')->cascadeOnDelete();
            $table->date('due_date')->index();
            $table->foreignId('reminder_setting_id')->constrained('reminder_settings')->cascadeOnDelete();
            $table->string('channel')->index(); // email, whatsapp, telegram, sms
            $table->string('status')->default('pending')->index(); // pending, sent, failed, skipped
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamps();

            // Composite Unique Constraint: Guarantees zero duplicate sends per cycle & wave
            $table->unique(['sponsor_id', 'due_date', 'reminder_setting_id', 'channel'], 'uk_sponsor_reminder');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
    }
};
