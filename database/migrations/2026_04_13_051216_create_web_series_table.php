<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebSeriesTable extends Migration
{
    public function up()
    {
        Schema::create('web_series', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('project_name');
            $table->string('category');
            $table->text('concept')->nullable();
            $table->integer('total_episodes')->default(0);
            $table->string('status')->default('series_created');
            $table->timestamps();
            
            // Add indexes
            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_series');
    }
}