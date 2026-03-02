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
            $table->string('capa_status', 20)->default('Draft')->after('status');
            $table->text('capa_root_cause')->nullable()->after('resolution_summary');
            $table->text('capa_corrective_action')->nullable()->after('capa_root_cause');
            $table->text('capa_preventive_action')->nullable()->after('capa_corrective_action');
            $table->date('capa_due_date')->nullable()->after('target_resolution_date');
            $table->timestamp('capa_submitted_at')->nullable()->after('capa_status');
            $table->timestamp('capa_approved_at')->nullable()->after('capa_submitted_at');
            $table->foreignId('capa_approved_by')->nullable()->after('capa_approved_at')->constrained('users')->nullOnDelete();
            $table->text('capa_rejected_reason')->nullable()->after('capa_approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropConstrainedForeignId('capa_approved_by');
            $table->dropColumn([
                'capa_status',
                'capa_root_cause',
                'capa_corrective_action',
                'capa_preventive_action',
                'capa_due_date',
                'capa_submitted_at',
                'capa_approved_at',
                'capa_rejected_reason',
            ]);
        });
    }
};
