<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id('documentID'); // Primary Key
            $table->string('docPath'); // Path of the document
            $table->unsignedBigInteger('ideaID'); // Foreign Key to idea table
            $table->timestamps(); // created_at & updated_at

            // Foreign key constraint
            $table->foreign('ideaID')
                  ->references('ideaID')
                  ->on('idea')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
