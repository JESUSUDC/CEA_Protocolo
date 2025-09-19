<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cellphones', function (Blueprint $table) {
            $table->string('id')->primary(); // UUID string
            $table->string('brand');
            $table->string('imei');
            $table->float('screen_size');
            $table->float('megapixels');
            $table->integer('ram_mb');
            $table->integer('storage_primary_mb');
            $table->integer('storage_secondary_mb')->nullable();
            $table->string('operating_system');
            $table->string('operator');
            $table->string('network_technology');
            $table->boolean('wifi')->default(false);
            $table->boolean('bluetooth')->default(false);
            $table->integer('camera_count');
            $table->string('cpu_brand');
            $table->float('cpu_speed_ghz');
            $table->boolean('nfc')->default(false);
            $table->boolean('fingerprint')->default(false);
            $table->boolean('ir')->default(false);
            $table->boolean('water_resistant')->default(false);
            $table->integer('sim_count');
            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cellphones');
    }
};
