<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScenesTable extends Migration
{
    public function up()
    {
        Schema::create('scenes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('episode_id');
            $table->unsignedBigInteger('web_series_id');
            $table->integer('scene_number');
            $table->string('title');
            $table->text('content');
            $table->text('summary')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
            
            // Add indexes
            $table->index('episode_id');
            $table->index('web_series_id');
            $table->index('scene_number');
            $table->index('status');
            
            // Add unique constraint
            $table->unique(['episode_id', 'scene_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('scenes');
    }
}