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
        Schema::table('scenes', function (Blueprint $table) {
            $table->string('video_url')->nullable()->after('generated_image_url');
            $table->string('video_status')->default('pending')->after('video_url');
            $table->timestamp('video_generation_started_at')->nullable()->after('video_status');
            $table->timestamp('video_generation_completed_at')->nullable()->after('video_generation_started_at');
            $table->text('video_error_message')->nullable()->after('video_generation_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->dropColumn([
                'video_url',
                'video_status',
                'video_generation_started_at',
                'video_generation_completed_at',
                'video_error_message'
            ]);
        });
    }
};