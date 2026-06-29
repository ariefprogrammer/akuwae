<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('driver_id');
            $table->string('ktp_number', 20);
            $table->string('sim_number', 30);
            $table->string('stnk_photo', 255);
            $table->string('selfie_ktp_photo', 255);
            $table->timestamps();
            
            $table->foreign('driver_id')
                  ->references('id')
                  ->on('drivers')
                  ->onDelete('cascade');
                  
            $table->unique('driver_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_documents');
    }
};