<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id('commentID');            // Primary Key
            $table->text('comment');            // Comment text
            $table->boolean('isAnonymous')->default(false); // Anonymous flag
            $table->unsignedBigInteger('ideaID');   // FK to idea
            $table->unsignedBigInteger('staffID');  // FK to staff
            $table->timestamps();               // created_at & updated_at

            // Foreign key constraints
            $table->foreign('ideaID')
                  ->references('ideaID')
                  ->on('idea')
                  ->onDelete('cascade');

            $table->foreign('staffID')
                  ->references('staffID')
                  ->on('staffs')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
