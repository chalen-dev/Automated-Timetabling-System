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
        Schema::create('professors', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');

            // Replace ENUM with string + optional check
            $table->string('professor_type')->default('regular'); //values ('regular', 'non-regular', 'none')

            $table->string('gender'); //values ('male','female','none')

            $table->decimal('max_unit_load', 10, 1);
            $table->integer('professor_age')->nullable();
            $table->string('position')->nullable();
            $table->foreignId('academic_program_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professors');
    }
};
