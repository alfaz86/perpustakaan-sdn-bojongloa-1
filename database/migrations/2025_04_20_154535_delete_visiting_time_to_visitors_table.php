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
        // Drop the 'visiting_time' column from the 'visitors' table if it exists
        if (Schema::hasColumn('visitors', 'visiting_time')) {
            Schema::table('visitors', function (Blueprint $table) {
                $table->dropColumn('visiting_time');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {}
};
