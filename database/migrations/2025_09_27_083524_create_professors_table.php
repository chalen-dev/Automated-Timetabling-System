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

        if (Schema::hasTable('professors')) {
            return;
        }

        Schema::create('professors', function (Blueprint $table) use ($driver) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('professor_type', ['regular', 'non-regular', 'none'])->default('regular');
            $table->enum('gender', ['male', 'female', 'none'])->default('none');
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
