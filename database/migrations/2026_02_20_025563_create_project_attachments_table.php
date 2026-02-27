<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->uuid('preview_id')->nullable();
            $table->uuid('feedback_id')->nullable();
            $table->string('file_name', 255);
            $table->string('file_url', 500);
            $table->enum('file_type', [
                'brief',
                'preview',
                'preview_source',
                'final_artwork',
                'final_source',
                'feedback_attachment',
                'other',
            ])->default('other');
            $table->integer('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->uuid('uploaded_by');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->softDeletes();
            $table->uuid('deleted_by')->nullable();

            $table->index('project_id', 'idx_project_id');
            $table->index('preview_id', 'idx_preview_id');
            $table->index('feedback_id', 'idx_feedback_id');
            $table->index('file_type', 'idx_file_type');
            $table->index('deleted_at', 'idx_deleted_at');

            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->cascadeOnDelete();

            $table->foreign('preview_id')
                  ->references('id')
                  ->on('project_previews')
                  ->cascadeOnDelete();

            $table->foreign('feedback_id')
                  ->references('id')
                  ->on('feedbacks')
                  ->cascadeOnDelete();

            $table->foreign('uploaded_by')
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
        Schema::dropIfExists('project_attachments');
    }
};
