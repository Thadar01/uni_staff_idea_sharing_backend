<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id('rolepermissionID');   // Primary Key

            $table->unsignedBigInteger('roleID');
            $table->unsignedBigInteger('permissionID');

            $table->timestamps();

            // Foreign Keys
            $table->foreign('roleID')
                  ->references('roleID')
                  ->on('roles')
                  ->onDelete('cascade');

            $table->foreign('permissionID')
                  ->references('permissionID')
                  ->on('permissions')
                  ->onDelete('cascade');

            // Prevent duplicate role-permission pairs
            $table->unique(['roleID', 'permissionID']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
