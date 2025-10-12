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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('course_title');
            $table->string('course_name');

            $table->string('course_type'); //values ('major', 'minor', 'pe', 'nstp', 'other')

            $table->integer('class_hours');
            $table->integer('total_lecture_class_days');
            $table->integer('total_laboratory_class_days');
            $table->decimal('unit_load', 10, 1);

            // Replaced ENUM with string + CHECK constraint
            $table->string('duration_type')->default('none'); //values ('semestral', 'term', 'none')

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
