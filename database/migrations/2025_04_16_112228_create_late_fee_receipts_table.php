<?php

use App\Models\LateFeeReceipt;
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
        Schema::create('late_fee_receipts', function (Blueprint $table) {
            $table->id();
            $table->mediumText('file_data')->charset('binary')->nullable();
            $table->string('file_name');
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size');
            $table->date('date');column: 
            $table->string('status')->default(LateFeeReceipt::PENDING);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('late_fee_receipts');
    }
};
