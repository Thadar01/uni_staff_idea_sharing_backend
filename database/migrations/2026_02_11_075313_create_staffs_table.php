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
        Schema::create('staffs', function (Blueprint $table) {
            $table->id('staffID');                      // PK
            $table->string('staffName');
            $table->string('staffPhNo')->unique();      // UNIQUE
            $table->string('staffEmail')->unique();     // UNIQUE
            $table->string('staffPassword');
            $table->date('staffDOB');
            $table->text('staffAddress');
            $table->string('staffProfile')->nullable();
            $table->boolean('termsAccepted')->default(false);
            $table->dateTime('termsAcceptedDate')->nullable();
            $table->dateTime('createdDateTime')->useCurrent();

        // Foreign Keys
            $table->unsignedBigInteger('departmentID');
            $table->unsignedBigInteger('roleID');

            $table->foreign('departmentID')
                ->references('departmentID')
                ->on('departments')
                ->onDelete('cascade');

            $table->foreign('roleID')
                ->references('roleID')
                ->on('roles')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staffs');
    }
};
