<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('closure_setting', function (Blueprint $table) {
            $table->id('settingID'); // Primary Key
            $table->date('closureDate'); // Closure Date
            $table->date('finalclosureDate'); // Final Closure Date
            $table->string('academicYear'); // Academic Year
            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('closure_setting');
    }    
};
