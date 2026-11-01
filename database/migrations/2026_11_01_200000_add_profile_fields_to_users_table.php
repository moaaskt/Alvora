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
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar', 2048)->nullable()->after('email');
            $table->text('bio')->nullable()->after('phone');
            $table->string('job_title', 120)->nullable()->after('bio');
            $table->string('department', 100)->nullable()->after('job_title');
            $table->string('linkedin_url', 255)->nullable()->after('department');
            $table->string('facebook_url', 255)->nullable()->after('linkedin_url');
            $table->string('twitter_url', 255)->nullable()->after('facebook_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar',
                'bio',
                'job_title',
                'department',
                'linkedin_url',
                'facebook_url',
                'twitter_url',
            ]);
        });
    }
};

