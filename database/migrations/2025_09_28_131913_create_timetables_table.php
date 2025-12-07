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

        Schema::create('timetables', function (Blueprint $table) use ($driver){
            $table->id();
            // User_id Foreign Key on Users table
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->string('timetable_name');
            $table->enum('semester', ['1st', '2nd'])->default('1st');
            $table->string('academic_year');
            $table->text('timetable_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};
