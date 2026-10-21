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
        Schema::create('ai_interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('risk_score_id')->nullable()->constrained('risk_scores')->nullOnDelete();
            $table->foreignId('recommended_lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
            $table->text('message');
            $table->string('status')->default('sent'); // sent, viewed, acknowledged
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_interventions');
    }
};
