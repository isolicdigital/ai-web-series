<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('role', ['admin', 'user'])->default('user');
            $table->foreignId('agency_id')->nullable();
            $table->boolean('is_agency_owner')->default(false);
            $table->string('photo')->nullable();
            $table->enum('theme', ['light', 'dark'])->default('light');
            $table->timestamp('last_seen')->nullable();
            $table->timestamps();
        });

        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('owner_user_id');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('agency_id')->references('id')->on('agencies')->nullOnDelete();
        });

        Schema::table('agencies', function (Blueprint $table) {
            $table->foreign('owner_user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('order')->default(0);
            $table->integer('validity_days')->default(999999);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('wp_id')->nullable();
            $table->string('zoo_id')->nullable();
            $table->string('lp_id')->nullable();
            $table->string('exp_id')->nullable();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('agencies');
        Schema::dropIfExists('users');
    }
};