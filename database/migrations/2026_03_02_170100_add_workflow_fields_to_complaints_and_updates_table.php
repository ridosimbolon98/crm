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
        Schema::table('complaints', function (Blueprint $table) {
            $table->string('action_type', 40)->nullable()->after('capa_status');
            $table->string('current_pool_department', 40)->default('qa')->after('action_type');
            $table->index('current_pool_department');
        });

        Schema::table('complaint_updates', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('complaint_id')->constrained('users')->nullOnDelete();
            $table->string('department', 40)->nullable()->after('author');
            $table->string('pool_to_department', 40)->nullable()->after('department');
            $table->index(['complaint_id', 'pool_to_department']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaint_updates', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['complaint_id', 'pool_to_department']);
            $table->dropColumn(['user_id', 'department', 'pool_to_department']);
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->dropIndex(['current_pool_department']);
            $table->dropColumn(['action_type', 'current_pool_department']);
        });
    }
};
