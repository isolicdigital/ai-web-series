<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_video_credits', function (Blueprint $table) {
            $table->integer('free_credits')->default(0)->after('total_credits');
            $table->integer('free_credits_used')->default(0)->after('free_credits');
            $table->integer('paid_credits')->default(0)->after('free_credits_used');
            $table->integer('paid_credits_used')->default(0)->after('paid_credits');
        });

        // Migrate existing data: treat existing credits as free
        DB::statement('UPDATE user_video_credits SET free_credits = total_credits, free_credits_used = used_credits');
    }

    public function down(): void
    {
        Schema::table('user_video_credits', function (Blueprint $table) {
            $table->dropColumn(['free_credits', 'free_credits_used', 'paid_credits', 'paid_credits_used']);
        });
    }
};