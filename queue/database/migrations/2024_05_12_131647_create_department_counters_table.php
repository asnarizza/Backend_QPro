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
        Schema::create('department_counters', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('staff_id');
            $table->foreign('staff_id')->references('id')->on('users');

            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments');

            $table->unsignedBigInteger('counter_id')->nullable();
            $table->foreign('counter_id')->references('id')->on('counters');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_counters');
    }
};
