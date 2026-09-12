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
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone'); // Normalized E.164 (+62...)
            $table->string('telegram_chat_id')->nullable()->index();
            $table->string('telegram_onboard_code', 32)->unique();
            $table->string('orphan_name')->nullable();
            $table->date('last_donation_date')->index();
            $table->string('frequency')->default('annual'); // annual, 6_months
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status')->default('active')->index(); // active, paused, cancelled
            $table->json('channel_preferences')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsors');
    }
};
