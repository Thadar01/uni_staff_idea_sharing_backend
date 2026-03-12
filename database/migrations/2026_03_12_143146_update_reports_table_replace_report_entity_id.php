<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('report_entity_id');

            $table->unsignedBigInteger('ideaID')->nullable()->after('status');
            $table->unsignedBigInteger('commentID')->nullable()->after('ideaID');

            $table->foreign('ideaID')
                  ->references('ideaID')
                  ->on('idea')
                  ->onDelete('cascade');

            $table->foreign('commentID')
                  ->references('commentID')
                  ->on('comments')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['ideaID']);
            $table->dropForeign(['commentID']);

            $table->dropColumn(['ideaID', 'commentID']);

            $table->unsignedBigInteger('report_entity_id')->after('status');
        });
    }
};