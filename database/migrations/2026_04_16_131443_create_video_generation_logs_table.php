<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('video_generation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('scene_id');
            $table->unsignedBigInteger('series_id');
            $table->string('prediction_id')->nullable();
            $table->string('status')->default('pending');
            $table->string('image_url')->nullable();
            $table->string('video_url')->nullable();
            $table->text('prompt')->nullable();
            $table->json('input_params')->nullable();
            $table->json('output_data')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('scene_id');
            $table->index('series_id');
            $table->index('prediction_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('video_generation_logs');
    }
};