<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idea', function (Blueprint $table) {
            $table->id('ideaID'); // Primary Key
            $table->string('title'); // Idea title
            $table->text('description'); // Idea description
            $table->boolean('isAnonymous')->default(false); // Boolean for anonymous
            $table->unsignedBigInteger('staffID'); // Foreign Key to staff
            $table->unsignedBigInteger('settingID'); // Foreign Key to closure_setting
            $table->string('status')->default('pending'); // Status of the idea
            $table->timestamps(); // created_at & updated_at

            // Foreign key constraints
            $table->foreign('staffID')
                  ->references('staffID')
                  ->on('staffs')
                  ->onDelete('cascade');

            $table->foreign('settingID')
                  ->references('settingID')
                  ->on('closure_setting')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idea');
    }
};

