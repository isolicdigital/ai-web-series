<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Comedy Categories Table
        Schema::create('comedy_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Comedy Templates Table
        Schema::create('comedy_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('comedy_categories')->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->string('init_image');
            $table->string('preview_image')->nullable();
            $table->string('reference_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamps();
        });

        // Comedy Comedians Table
        Schema::create('comedy_comedians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('comedy_templates')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('comedy_categories')->nullOnDelete();
            $table->string('name');
            $table->enum('origin_type', ['system', 'custom', 'template_based'])->default('system');
            $table->string('avatar_image')->nullable();
            $table->string('generation_status')->default('pending');
            $table->json('generation_metadata')->nullable();
            $table->timestamps();
        });

        // Comedy Videos Table
        Schema::create('comedy_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('comedian_id')->nullable()->constrained('comedy_comedians')->nullOnDelete();
            $table->foreignId('category_id')->constrained('comedy_categories');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('video_url');
            $table->string('thumbnail_url')->nullable();
            $table->string('processing_status')->default('pending');
            $table->integer('duration_seconds')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comedy_videos');
        Schema::dropIfExists('comedy_comedians');
        Schema::dropIfExists('comedy_templates');
        Schema::dropIfExists('comedy_categories');
    }
};