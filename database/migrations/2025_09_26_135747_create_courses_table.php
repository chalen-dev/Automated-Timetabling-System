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
        $driver = Schema::getConnection()->getDriverName(); // mysql, sqlite, etc.

        Schema::create('courses', function (Blueprint $table) use ($driver) {
            $table->id();
            $table->string('course_title');
            $table->string('course_name');
            $table->integer('class_hours');
            $table->integer('total_lecture_class_days');
            $table->integer('total_laboratory_class_days');
            $table->decimal('unit_load', 10, 1);
            $table->enum('course_type', ['major', 'minor', 'pe', 'nstp', 'other']);
            $table->enum('duration_type', ['semestral', 'term', 'none'])->default('none');
            $table->foreignId('academic_program_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
