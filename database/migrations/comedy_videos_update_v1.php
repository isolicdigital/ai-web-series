<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comedy_videos', function (Blueprint $table) {
            $table->text('joke')->nullable()->after('title');
            $table->foreignId('template_id')->nullable()->after('category_id')->constrained('comedy_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('comedy_videos', function (Blueprint $table) {
            $table->dropColumn('joke');
            $table->dropForeign(['template_id']);
            $table->dropColumn('template_id');
        });
    }
};