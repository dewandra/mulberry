<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('thumbnail_url', 500)->nullable()->after('current_preview_id');
            $table->string('thumbnail_filename', 255)->nullable()->after('thumbnail_url');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['thumbnail_url', 'thumbnail_filename']);
        });
    }
};
