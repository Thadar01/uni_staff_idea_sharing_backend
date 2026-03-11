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
        Schema::table('documents', function (Blueprint $table) {
            $table->string('fileType')->nullable()->after('docPath');
            $table->integer('fileSize')->default(0)->after('fileType');
            $table->boolean('isHidden')->default(false)->after('fileSize');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['fileType', 'fileSize', 'isHidden']);
        });
    }
};
