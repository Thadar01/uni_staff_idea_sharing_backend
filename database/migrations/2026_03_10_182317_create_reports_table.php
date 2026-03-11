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
        Schema::create('reports', function (Blueprint $table) {
            $table->id('report_id'); // Primary Key

            $table->enum('report_type', ['idea', 'comment']); // Type of reported entity
            $table->text('reason'); // Reason for report
            $table->enum('status', ['pending', 'resolved', 'dismissed'])->default('pending'); // Status
            $table->unsignedBigInteger('report_entity_id'); // FK to idea or comment
            $table->unsignedBigInteger('reporter_id'); // FK to staff
            $table->unsignedBigInteger('resolved_by')->nullable(); // FK to staff who resolved
            $table->timestamps(); // created_at & updated_at

            // Foreign key constraints
            $table->foreign('reporter_id')
                  ->references('staffID')
                  ->on('staffs')
                  ->onDelete('cascade');

            $table->foreign('resolved_by')
                  ->references('staffID')
                  ->on('staffs')
                  ->nullOnDelete(); // nullable FK
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
