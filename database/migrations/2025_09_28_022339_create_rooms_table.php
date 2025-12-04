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

            // Replace ENUM with string + CHECK
            $table->string('room_type'); //values ('lecture', 'comlab', 'gym', 'main')

            $table->string('course_type_exclusive_to'); //values ('none', 'pe', 'nstp', 'others')

            $table->integer('room_capacity')->nullable();
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
