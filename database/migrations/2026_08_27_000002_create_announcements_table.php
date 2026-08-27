<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title');
            $table->string('position_code')->nullable();
            $table->string('category');
            $table->string('location');
            $table->string('contract_type');
            $table->longText('description');
            $table->text('requirements_formal');
            $table->text('requirements_merit')->nullable();
            $table->text('scope_of_duties');
            $table->json('required_documents');
            $table->timestamp('deadline_at');
            $table->string('status')->default('draft'); // draft, pending_approval, published, expired, cancelled
            $table->integer('version')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
