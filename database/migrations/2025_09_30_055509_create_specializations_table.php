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
        if (Schema::hasTable('specializations')) {
            return;
        }
        Schema::create('specializations', function (Blueprint $table) {
            $table->id();

            //Professor Foreign Key
            $table->foreignId('professor_id')
            ->constrained('professors')
            ->onDelete('cascade');

            //Course Foreign Key
            $table->foreignId('course_id')
            ->constrained('courses')
            ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specializations');
    }
};
