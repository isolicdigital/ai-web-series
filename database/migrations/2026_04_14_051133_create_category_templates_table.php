<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('category_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->json('category_prompt')->nullable();
            $table->text('style_prompt')->nullable();
            $table->text('lighting_prompt')->nullable();
            $table->text('camera_prompt')->nullable();
            $table->text('color_prompt')->nullable();
            $table->text('mood_prompt')->nullable();
            $table->string('init_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('category_id');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('category_templates');
    }
}