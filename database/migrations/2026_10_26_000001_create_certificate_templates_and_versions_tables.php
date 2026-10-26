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
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            // company_id nullable para suporte a templates globais da plataforma ALVORA
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('status', 32)->default('active'); // 'draft', 'active', 'archived'
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['is_default', 'status']);
        });

        Schema::create('certificate_template_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('certificate_templates')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->json('schema_json');
            $table->string('status', 32)->default('active'); // 'draft', 'active', 'deprecated'
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['template_id', 'version']);
            $table->index(['template_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_template_versions');
        Schema::dropIfExists('certificate_templates');
    }
};
