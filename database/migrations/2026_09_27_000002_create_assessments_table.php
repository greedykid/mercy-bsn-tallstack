<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lpk_id')->constrained('lpks')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('assessment_type');
            $table->date('start_at');
            $table->date('end_at');
            $table->string('location')->nullable();
            $table->string('lead_assessor')->nullable();
            $table->text('assessor_team')->nullable();
            $table->string('status')->default('PLANNED')->index();
            $table->tinyInteger('tp_deadline_months')->default(2);
            $table->date('tp_due_date')->nullable()->index();
            $table->integer('tp_findings_total')->default(0);
            $table->integer('tp_findings_closed')->default(0);
            $table->boolean('tp_has_extension')->default(false);
            $table->tinyInteger('tp_extension_months')->default(0);
            $table->string('tp_extension_letter_no')->nullable();
            $table->date('tp_extended_due_date')->nullable();
            $table->date('tp_submitted_at')->nullable();
            $table->date('tp_satisfied_at')->nullable();
            $table->date('assessment_report_date')->nullable();
            $table->date('eha_scheduled_date')->nullable();
            $table->date('eha_completed_at')->nullable();
            $table->text('eha_notes')->nullable();
            $table->date('sk_issued_at')->nullable();
            $table->string('sk_number')->nullable();
            $table->integer('sk_lead_time_days')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
