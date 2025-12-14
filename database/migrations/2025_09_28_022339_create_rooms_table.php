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

        if (Schema::hasTable('rooms')) {
            return;
        }
        Schema::create('rooms', function (Blueprint $table) use ($driver){
            $table->id();
            $table->string('room_name');
            $table->enum('room_type', ['lecture', 'comlab', 'gym', 'main'])->default('lecture');
            $table->enum('course_type_exclusive_to', ['none', 'pe', 'nstp', 'others'])->default('none');
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
