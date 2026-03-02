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
        Schema::create('complaint_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 40)->default('note');
            $table->string('status_before', 30)->nullable();
            $table->string('status_after', 30)->nullable();
            $table->string('author', 120)->nullable();
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
        Schema::dropIfExists('complaint_updates');
    }
};
