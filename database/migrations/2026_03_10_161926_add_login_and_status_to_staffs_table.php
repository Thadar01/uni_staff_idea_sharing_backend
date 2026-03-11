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
        Schema::table('staffs', function (Blueprint $table) {

            // Last login timestamp (nullable = never logged in yet)
            $table->dateTime('last_login_at')->nullable()->after('createdDateTime');

            // Account status
            $table->enum('account_status', ['active', 'disabled'])
                  ->default('active')
                  ->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staffs', function (Blueprint $table) {
            
            $table->dropColumn(['last_login_at', 'account_status']);
        
        });
    }
};
