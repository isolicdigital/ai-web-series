<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comedy_templates', function (Blueprint $table) {
            $table->string('preview_video')->nullable()->after('preview_image');
            $table->text('joke_template')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('comedy_templates', function (Blueprint $table) {
            $table->dropColumn('preview_video');
            $table->dropColumn('joke_template');
        });
    }
};