<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_reminder_sponsor', function (Blueprint $table) {
            $table->foreignId('custom_reminder_id')->constrained('custom_reminders')->cascadeOnDelete();
            $table->foreignId('sponsor_id')->constrained('sponsors')->cascadeOnDelete();
            $table->primary(['custom_reminder_id', 'sponsor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_reminder_sponsor');
    }
};
