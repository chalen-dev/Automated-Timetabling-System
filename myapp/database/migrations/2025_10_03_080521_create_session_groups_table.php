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
        Schema::create('session_groups', function (Blueprint $table) {
            $table->id();
            $table->string('session_name');
            $table->enum('year_level', ['1st', '2nd', '3rd', '4th']);
            $table->text('short_description')->nullable();
            $table->foreignId('academic_program_id')->constrained('academic_programs')->cascadeOnDelete();
            $table->foreignId('timetable_id')->constrained('timetables')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_groups');
    }
};
