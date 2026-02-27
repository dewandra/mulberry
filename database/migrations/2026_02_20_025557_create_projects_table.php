<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('project_name');
            $table->string('project_code', 50)->unique();
            $table->uuid('client_id');
            $table->enum('status', [
                'brief',
                'scheduled',
                'work_in_progress',
                'preview_sent',
                'feedback_received',
                'artwork_approved',
                'final_artwork_preparation',
                'fa_sent',
                'project_closed',
            ])->default('brief');
            $table->enum('priority', ['high', 'normal', 'low'])->default('normal');
            $table->text('description')->nullable();
            $table->date('deadline')->nullable();
            $table->date('report_date')->nullable();
            $table->timestamp('artwork_approved_at')->nullable();
            $table->uuid('current_preview_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->softDeletes();
            $table->uuid('deleted_by')->nullable();

            $table->index('client_id', 'idx_client_id');
            $table->index('status', 'idx_status');
            $table->index('priority', 'idx_priority');
            $table->index('deadline', 'idx_deadline');
            $table->index('deleted_at', 'idx_deleted_at');
            $table->index('project_code', 'idx_project_code');

            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->cascadeOnDelete();

            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            $table->foreign('updated_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            $table->foreign('deleted_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
