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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_name');
            $table->enum('room_type', ['lecture', 'comlab', 'gym', 'main']);
            $table->enum('course_type_exclusive_to', ['none', 'pe', 'nstp', 'others']);
            $table->integer('room_capacity')->nullable();
            $table->boolean('monday_exclusive');
            $table->boolean('tuesday_exclusive');
            $table->boolean('wednesday_exclusive');
            $table->boolean('thursday_exclusive');
            $table->boolean('friday_exclusive');
            $table->boolean('saturday_exclusive');
            $table->boolean('sunday_exclusive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
