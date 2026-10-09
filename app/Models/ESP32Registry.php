<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['device', 'temperature', 'humidity', 'status_read_dht11', 'status_led_01', 'status_led_02'])]
class ESP32Registry extends Model
{
    protected $table = 'esp32_dht11_leds';
}
