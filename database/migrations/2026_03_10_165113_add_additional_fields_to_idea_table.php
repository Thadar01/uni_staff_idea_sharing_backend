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
        Schema::table('idea', function (Blueprint $table) {
            // number of views
            $table->integer('viewCount')
                  ->default(0)
                  ->after('status');

            // reported / flagged idea
            $table->boolean('isFlagged')
                  ->default(false)
                  ->after('viewCount');

            // enable or disable comments
            $table->boolean('isCommentEnabled')
                  ->default(true)
                  ->after('isFlagged');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('idea', function (Blueprint $table) {
            $table->dropColumn([
            'viewCount',
            'isFlagged',
            'isCommentEnabled'
        ]);
        });
    }
};


