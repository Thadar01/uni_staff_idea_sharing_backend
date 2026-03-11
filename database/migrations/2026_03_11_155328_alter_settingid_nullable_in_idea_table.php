<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('idea', function (Blueprint $table) {
            $table->dropForeign(['settingID']);
        });

        Schema::table('idea', function (Blueprint $table) {
            $table->unsignedBigInteger('settingID')->nullable()->change();
        });

        Schema::table('idea', function (Blueprint $table) {
            $table->foreign('settingID')
                ->references('settingID')
                ->on('closure_setting')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('idea', function (Blueprint $table) {
            $table->dropForeign(['settingID']);
        });

        Schema::table('idea', function (Blueprint $table) {
            $table->unsignedBigInteger('settingID')->nullable(false)->change();
        });

        Schema::table('idea', function (Blueprint $table) {
            $table->foreign('settingID')
                ->references('settingID')
                ->on('closure_setting')
                ->onDelete('cascade');
        });
    }
};