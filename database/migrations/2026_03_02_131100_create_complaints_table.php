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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('customer_name');
            $table->string('customer_phone', 40)->nullable();
            $table->string('customer_email')->nullable();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('complaint_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('complaint_channel', 40)->default('Phone');
            $table->string('production_code', 80)->nullable();
            $table->date('complaint_date');
            $table->string('severity', 20)->default('Medium');
            $table->string('status', 30)->default('Open');
            $table->string('assigned_to', 120)->nullable();
            $table->date('target_resolution_date')->nullable();
            $table->text('description');
            $table->text('resolution_summary')->nullable();
            $table->string('compensation_type', 60)->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'severity']);
            $table->index(['brand_id', 'complaint_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
