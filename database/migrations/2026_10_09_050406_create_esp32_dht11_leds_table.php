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
        Schema::create('esp32_dht11_leds', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('device');
            $table->float('temperature');
            $table->integer('humidity');
            $table->string('status_read_dht11');
            $table->string('status_led_01');
            $table->string('status_led_02');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('esp32_dht11_leds');
    }
};
