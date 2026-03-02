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
        Schema::create('complaint_replacement_progresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('department', 40);
            $table->string('item_name', 180);
            $table->unsignedInteger('quantity');
            $table->string('delivery_note_number', 120);
            $table->text('note')->nullable();
            $table->timestamp('event_at');
            $table->timestamps();

            $table->index(['complaint_id', 'event_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_replacement_progresses');
    }
};
