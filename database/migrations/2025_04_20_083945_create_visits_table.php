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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->onDelete('cascade');
            $table->timestamp('visiting_time')->useCurrent();
            $table->timestamps();
        });

        // seed data from visitors table to visits table
        DB::table('visits')->insert(
            DB::table('visitors')->get()->map(function ($visitor) {
                return [
                    'visitor_id' => $visitor->id,
                    'visiting_time' => $visitor->visiting_time,
                    'created_at' => $visitor->created_at,
                    'updated_at' => $visitor->updated_at,
                ];
            })->toArray()
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
