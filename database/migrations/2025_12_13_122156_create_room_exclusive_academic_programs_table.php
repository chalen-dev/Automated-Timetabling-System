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
        if (Schema::hasTable('room_exclusive_academic_programs')) {
            return;
        }
        Schema::create('room_exclusive_academic_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_program_id')->constrained()->onDelete('cascade');
            $table->unique(
                ['room_id', 'academic_program_id'],
                'room_program_unique'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_exclusive_academic_programs');
    }
};
