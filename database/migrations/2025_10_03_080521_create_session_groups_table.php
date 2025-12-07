<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_groups', function (Blueprint $table)  {
            $table->id();
            $table->string('session_name');
            $table->enum('year_level', ['1st','2nd','3rd','4th'])->default('1st');
            $table->text('short_description')->nullable();
            $table->string('session_color')->nullable();
            $table->foreignId('academic_program_id')->constrained('academic_programs')->cascadeOnDelete();
            $table->foreignId('timetable_id')->constrained('timetables')->cascadeOnDelete();
            $table->unique(
                [
                'timetable_id',
                'academic_program_id',
                'year_level',
                'session_name'
                ],
                'session_groups_unique_combo'
            );

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_groups');
    }
};

