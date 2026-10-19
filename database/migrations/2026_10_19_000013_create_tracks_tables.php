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
        Schema::create('learning_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('department')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'slug']);
            $table->index(['company_id', 'department']);
        });

        Schema::create('track_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained('learning_tracks')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->unsignedInteger('order')->default(1);
            $table->timestamps();

            $table->unique(['track_id', 'course_id']);
            $table->index(['track_id', 'order']);
        });

        Schema::create('track_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('track_id')->constrained('learning_tracks')->cascadeOnDelete();
            $table->string('status')->default('active'); // active, completed
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'track_id']);
            $table->index(['company_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('track_enrollments');
        Schema::dropIfExists('track_courses');
        Schema::dropIfExists('learning_tracks');
    }
};
