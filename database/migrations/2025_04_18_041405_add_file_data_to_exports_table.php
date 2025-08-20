<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exports', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                $table->binary('file_data')->nullable()->after('file_name');
            } else {
                $table->mediumText('file_data')->charset('binary')->nullable()->after('file_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exports', function (Blueprint $table) {
            $table->dropColumn('file_data');
        });
    }
};
