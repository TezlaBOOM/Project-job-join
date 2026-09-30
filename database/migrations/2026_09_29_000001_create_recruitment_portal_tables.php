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
        Schema::create('login_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('code_hash');
            $table->string('attempt_id')->index();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->string('ip', 45);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('contract_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('job_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_template')->default(true);
            $table->timestamps();
        });

        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->string('key');
            $table->string('type'); // text, textarea, email, tel, number, date, select, checkbox, switch, statement, file
            $table->string('label');
            $table->string('help_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_system')->default(false);
            $table->json('validation')->nullable();
            $table->json('options')->nullable();
            $table->string('section')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('filters', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('source'); // dictionary, field
            $table->string('source_ref'); // department, contract_type, job_category, location, working_time, deadline_at, search
            $table->string('control_type'); // select, checkbox, text, date_range
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('consent_texts', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->longText('content');
            $table->timestamp('active_from');
            $table->timestamps();
        });

        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->string('slug')->unique();
            $table->string('title');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('contract_type_id')->nullable()->constrained('contract_types')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('job_categories')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('working_time')->default('Pełny etat');
            $table->longText('description');
            $table->text('requirements')->nullable();
            $table->text('nice_to_have')->nullable();
            $table->text('offer_text')->nullable();
            $table->text('required_documents')->nullable();
            $table->foreignId('form_id')->nullable()->constrained('forms')->nullOnDelete();
            $table->json('statements')->nullable();
            $table->timestamp('deadline_at');
            $table->timestamp('published_at')->nullable();
            $table->string('status')->default('draft'); // draft, published, completed, archived
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'deadline_at']);
            $table->index(['department_id', 'contract_type_id']);
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->string('reference_code')->unique();
            $table->foreignId('job_offer_id')->constrained('job_offers')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->json('answers')->nullable();
            $table->string('status')->default('new'); // new, under_review, interview, rejected, qualified, cancelled
            $table->text('internal_notes')->nullable();
            $table->foreignId('consent_version_id')->nullable()->constrained('consent_texts')->nullOnDelete();
            $table->timestamp('consent_at');
            $table->boolean('future_consent')->default(false);
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_token_hash')->nullable()->index();
            $table->timestamp('cancel_token_expires_at')->nullable();
            $table->string('source')->default('web'); // web, manual
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['job_offer_id', 'status']);
            $table->index(['email', 'job_offer_id']);
        });

        Schema::create('application_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('type')->default('other'); // cv, letter, other
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('mime')->default('application/pdf');
            $table->unsignedBigInteger('size');
            $table->string('checksum');
            $table->string('scan_status')->default('clean'); // clean, pending, infected
            $table->timestamps();
        });

        Schema::create('application_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('from_status');
            $table->string('to_status');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('mail_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
            $table->string('template');
            $table->string('to_email');
            $table->string('subject');
            $table->string('status')->default('sent');
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->useCurrent();
        });

        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('subject');
            $table->text('body_html');
            $table->text('body_text');
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip', 45);
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['auditable_type', 'auditable_id']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->longText('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('mail_logs');
        Schema::dropIfExists('application_status_history');
        Schema::dropIfExists('application_files');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('job_offers');
        Schema::dropIfExists('consent_texts');
        Schema::dropIfExists('filters');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('forms');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('job_categories');
        Schema::dropIfExists('contract_types');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('login_codes');
    }
};
