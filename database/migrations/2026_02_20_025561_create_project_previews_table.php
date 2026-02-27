<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_previews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->string('version', 10);
            $table->string('title', 255)->nullable();
            $table->text('description');
            $table->text('internal_notes')->nullable();
            $table->timestamp('review_deadline')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->uuid('sent_by');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->uuid('deleted_by')->nullable();

            $table->unique(['project_id', 'version'], 'unique_project_version');
            $table->index('project_id', 'idx_project_id');
            $table->index('version', 'idx_version');
            $table->index('deleted_at', 'idx_deleted_at');

            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->cascadeOnDelete();

            $table->foreign('sent_by')
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
        Schema::dropIfExists('project_previews');
    }
};
