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
        Schema::create('customer_queues', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('counter_id');
            $table->foreign('counter_id')->references('id')->on('counters');
            
            $table->string('queue_number');
            $table->unsignedInteger('current_queue')->default(1);
            $table->timestamp('last_generated_at')->nullable();

            $table->timestamp('joined_at')->nullable(); 
            $table->timestamp('serviced_at')->nullable();

            $table->date('last_reset_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_queues');
    }
};
