<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id('voteID');                  // Primary Key
            $table->enum('voteType', ['Like', 'Unlike']); // Like or Unlike
            $table->unsignedBigInteger('staffID'); // FK to staff
            $table->unsignedBigInteger('ideaID');  // FK to idea
            $table->timestamps();                  // created_at & updated_at

            // Foreign key constraints
            $table->foreign('staffID')
                  ->references('staffID')
                  ->on('staffs')
                  ->onDelete('cascade');

            $table->foreign('ideaID')
                  ->references('ideaID')
                  ->on('idea')
                  ->onDelete('cascade');

            // Optional: Prevent same staff from voting multiple times on same idea
            $table->unique(['staffID', 'ideaID']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
