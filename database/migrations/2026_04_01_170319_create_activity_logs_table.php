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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id('log_id');
            // user_id can be null if a user is not authenticated yet.
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('url');
            $table->string('method');
            $table->text('user_agent')->nullable();
            $table->string('browser')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('staffID')->on('staffs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
