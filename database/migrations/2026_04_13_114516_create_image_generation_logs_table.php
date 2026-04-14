<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImageGenerationLogsTable extends Migration
{
    public function up()
    {
        Schema::create('image_generation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_id')->unique();
            $table->foreignId('scene_id')->constrained()->onDelete('cascade');
            $table->foreignId('web_series_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Request data
            $table->text('prompt');
            $table->string('model_id')->default('flux-2-dev');
            $table->integer('samples')->default(1);
            $table->integer('num_inference_steps')->default(30);
            $table->float('guidance_scale')->default(7.5);
            
            // Response data
            $table->string('api_request_id')->nullable();
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->text('image_urls')->nullable();
            $table->text('error_message')->nullable();
            $table->text('full_api_response')->nullable();
            
            // Webhook data
            $table->string('webhook_url')->nullable();
            $table->timestamp('webhook_received_at')->nullable();
            $table->text('webhook_payload')->nullable();
            
            // Timestamps
            $table->timestamp('api_called_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('attempts')->default(0);
            $table->integer('retry_count')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('tracking_id');
            $table->index('scene_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('image_generation_logs');
    }
}