<?php

use App\Models\ReportUpload;
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
        Schema::create('report_uploads', function (Blueprint $table) {
            $table->id();
            if (DB::getDriverName() === 'pgsql') {
                $table->binary('file_data')->nullable();
            } else {
                $table->mediumBlob('file_data')->nullable();
            }
            $table->string('file_name');
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size');
            $table->date('date');
            $table->string('status')->default(ReportUpload::PENDING);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_uploads');
    }
};
