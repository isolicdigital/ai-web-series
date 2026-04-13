<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEpisodesTable extends Migration
{
    public function up()
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('web_series_id');
            $table->integer('episode_number');
            $table->string('title');
            $table->text('prompt')->nullable();
            $table->text('concept')->nullable();
            $table->text('content')->nullable();
            $table->text('summary')->nullable();
            $table->integer('total_scenes')->default(0);
            $table->string('status')->default('concept_ready');
            $table->timestamps();
            
            // Add indexes
            $table->index('web_series_id');
            $table->index('episode_number');
            $table->index('status');
            
            // Add unique constraint
            $table->unique(['web_series_id', 'episode_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('episodes');
    }
}