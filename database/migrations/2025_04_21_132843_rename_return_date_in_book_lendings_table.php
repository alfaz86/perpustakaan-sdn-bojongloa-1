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
        Schema::table('book_lendings', function (Blueprint $table) {
            $table->renameColumn('return_date', 'due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_lendings', function (Blueprint $table) {
            $table->renameColumn('due_date', 'return_date');
        });
    }
};
