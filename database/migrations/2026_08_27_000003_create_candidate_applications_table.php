<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('announcement_id')->constrained('announcements')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('status')->default('submitted'); // submitted, formal_eval, qualified, rejected, interview, selected, withdrawn
            $table->string('tracking_token')->unique();
            $table->boolean('is_withdrawn')->default(false);
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();
        });

        Schema::create('application_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_application_id')->constrained('candidate_applications')->cascadeOnDelete();
            $table->string('file_type'); // cv, cover_letter, statement, certificate
            $table->string('file_path');
            $table->string('file_name');
            $table->integer('file_size');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('rodo_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_application_id')->constrained('candidate_applications')->cascadeOnDelete();
            $table->text('consent_text');
            $table->timestamp('agreed_at')->useCurrent();
            $table->string('ip_address');
            $table->timestamp('requested_erasure_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rodo_consents');
        Schema::dropIfExists('application_attachments');
        Schema::dropIfExists('candidate_applications');
    }
};
