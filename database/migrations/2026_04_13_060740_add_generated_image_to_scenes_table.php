<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGeneratedImageToScenesTable extends Migration
{
    public function up()
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->text('generated_image_url')->nullable()->after('image_prompt');
        });
    }

    public function down()
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->dropColumn('generated_image_url');
        });
    }
}