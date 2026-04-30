<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('color')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['workspace_id', 'project_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_statuses');
    }
};
