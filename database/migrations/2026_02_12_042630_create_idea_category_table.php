<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 public function up(): void
    {
        Schema::create('idea_category', function (Blueprint $table) {
   
            $table->id('ideaCatID'); // Primary Key
            $table->unsignedBigInteger('ideaID'); // Foreign Key to ideas table
            $table->unsignedBigInteger('categoryID'); // Foreign Key to categories table
            $table->timestamps(); // Optional: created_at & updated_at

            // Foreign key constraints
            $table->foreign('ideaID')
                  ->references('ideaID')
                  ->on('idea')
                  ->onDelete('cascade');

            $table->foreign('categoryID')
                  ->references('categoryID')
                  ->on('categories')
                  ->onDelete('cascade');
        

            // Optional: ensure uniqueness to avoid duplicate entries
            $table->unique(['ideaID', 'categoryID']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idea_category');
    }
};


