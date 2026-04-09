<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix cascade delete for comedy_jokes
        Schema::table('comedy_jokes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('comedy_jokes', function (Blueprint $table) {
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
        
        // Fix cascade delete for comedy_videos
        Schema::table('comedy_videos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('comedy_videos', function (Blueprint $table) {
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
        
        // Fix cascade delete for video_credit_purchases
        Schema::table('video_credit_purchases', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('video_credit_purchases', function (Blueprint $table) {
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
        
        // Fix cascade delete for user_video_credits
        Schema::table('user_video_credits', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('user_video_credits', function (Blueprint $table) {
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Restore comedy_jokes
        Schema::table('comedy_jokes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('comedy_jokes', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        
        // Restore comedy_videos
        Schema::table('comedy_videos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('comedy_videos', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        
        // Restore video_credit_purchases
        Schema::table('video_credit_purchases', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('video_credit_purchases', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        
        // Restore user_video_credits
        Schema::table('user_video_credits', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('user_video_credits', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};