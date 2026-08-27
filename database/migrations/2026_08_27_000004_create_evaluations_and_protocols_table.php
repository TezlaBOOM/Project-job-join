<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations_formal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_application_id')->constrained('candidate_applications')->cascadeOnDelete();
            $table->foreignId('recruiter_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_complete');
            $table->boolean('meets_requirements');
            $table->text('justification')->nullable();
            $table->string('result'); // passed, failed
            $table->timestamps();
        });

        Schema::create('evaluations_merit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_application_id')->constrained('candidate_applications')->cascadeOnDelete();
            $table->foreignId('recruiter_id')->constrained('users')->cascadeOnDelete();
            $table->integer('score')->default(0);
            $table->text('interview_notes')->nullable();
            $table->string('recommendation')->nullable();
            $table->timestamps();
        });

        Schema::create('protocols', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->cascadeOnDelete();
            $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();
            $table->text('summary');
            $table->string('pdf_path')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('protocols');
        Schema::dropIfExists('evaluations_merit');
        Schema::dropIfExists('evaluations_formal');
    }
};
