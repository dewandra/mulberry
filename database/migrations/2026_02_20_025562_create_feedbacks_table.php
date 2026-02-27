<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->uuid('preview_id')->nullable();
            $table->text('comment');
            $table->uuid('submitted_by');
            $table->timestamp('submitted_at')->useCurrent();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->uuid('deleted_by')->nullable();

            $table->index('project_id', 'idx_project_id');
            $table->index('preview_id', 'idx_preview_id');
            $table->index('deleted_at', 'idx_deleted_at');
            $table->index('submitted_at', 'idx_submitted_at');

            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->cascadeOnDelete();

            $table->foreign('preview_id')
                  ->references('id')
                  ->on('project_previews')
                  ->nullOnDelete();

            $table->foreign('submitted_by')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->foreign('deleted_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
