<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImagePromptToScenesTable extends Migration
{
    public function up()
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->text('image_prompt')->nullable()->after('content');
        });
    }

    public function down()
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->dropColumn('image_prompt');
        });
    }
}