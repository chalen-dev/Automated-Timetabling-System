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
        Schema::create('room_exclusive_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();

            // Replace ENUM with string + CHECK constraint
            $table->string('exclusive_day'); //values ('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_exclusive_days');
    }
};
