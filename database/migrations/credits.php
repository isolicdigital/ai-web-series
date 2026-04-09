<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Video Credit Plans Table
        Schema::create('video_credit_plans', function (Blueprint $table) {
            $table->id();
            $table->string('wp_id')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->integer('video_credits');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Video Credit Purchases Table
        Schema::create('video_credit_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('transaction_id')->nullable();
            $table->integer('credits_purchased');
            $table->integer('credits_used')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // User Video Credits Table
        Schema::create('user_video_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('total_credits')->default(0);
            $table->integer('used_credits')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_video_credits');
        Schema::dropIfExists('video_credit_purchases');
        Schema::dropIfExists('video_credit_plans');
    }
};