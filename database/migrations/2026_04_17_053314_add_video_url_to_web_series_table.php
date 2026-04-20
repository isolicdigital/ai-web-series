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
        Schema::table('web_series', function (Blueprint $table) {
            $table->string('video_url')->nullable()->after('concept');
            $table->string('video_status')->default('pending')->after('video_url');
            $table->timestamp('video_generated_at')->nullable()->after('video_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_series', function (Blueprint $table) {
            $table->dropColumn(['video_url', 'video_status', 'video_generated_at']);
        });
    }
};