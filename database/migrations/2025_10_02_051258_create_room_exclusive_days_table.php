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

        if (Schema::hasTable('room_exclusive_days')) {
            return;
        }
        Schema::create('room_exclusive_days', function (Blueprint $table) use ($driver) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->enum('exclusive_day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])->default('monday');
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
