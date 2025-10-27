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
    Schema::create('bets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('event_id')->constrained()->onDelete('cascade');
        $table->string('outcome');
        $table->decimal('amount', 12, 2);
        $table->enum('status', ['placed','won','lost'])->default('placed');
        $table->string('idempotency_key')->nullable()->unique();
        $table->timestamps();

        $table->index(['user_id', 'event_id']); // Composite index for user-event queries
        $table->index('status'); // Index for faster status filtering
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bets');
    }
};
