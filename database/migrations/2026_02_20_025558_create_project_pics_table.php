<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_pics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->uuid('pic_user_id');
            $table->timestamp('assigned_at')->useCurrent();
            $table->uuid('assigned_by')->nullable();

            $table->unique(['project_id', 'pic_user_id'], 'unique_project_pic');
            $table->index('pic_user_id', 'idx_pic_user_id');
            $table->index('project_id', 'idx_project_id');

            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->cascadeOnDelete();

            $table->foreign('pic_user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->foreign('assigned_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_pics');
    }
};
