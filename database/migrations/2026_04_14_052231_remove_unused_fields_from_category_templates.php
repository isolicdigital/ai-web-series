<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUnusedFieldsFromCategoryTemplates extends Migration
{
    public function up()
    {
        Schema::table('category_templates', function (Blueprint $table) {
            $table->dropColumn([
                'style_prompt',
                'lighting_prompt',
                'camera_prompt',
                'color_prompt',
                'mood_prompt'
            ]);
        });
    }

    public function down()
    {
        Schema::table('category_templates', function (Blueprint $table) {
            $table->text('style_prompt')->nullable()->after('category_prompt');
            $table->text('lighting_prompt')->nullable()->after('style_prompt');
            $table->text('camera_prompt')->nullable()->after('lighting_prompt');
            $table->text('color_prompt')->nullable()->after('camera_prompt');
            $table->text('mood_prompt')->nullable()->after('color_prompt');
        });
    }
}