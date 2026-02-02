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
        Schema::create('personalizations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('start_date');
            $table->integer('duration_month');
            $table->integer('control_freq_value');
            $table->enum('control_freq_unit', ['days', 'weeks', 'months']);
            $table->date('last_checkup_date')->nullable();
            $table->date('next_checkup_date')->nullable();
            $table->time('reminder_time');
            $table->enum('time_category', ['pagi', 'siang', 'sore', 'malam']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personalizations');
    }
};
