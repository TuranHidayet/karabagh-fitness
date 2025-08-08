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
    Schema::dropIfExists('trainers');
}

public function down(): void
{
    Schema::create('trainers', function (Blueprint $table) {
        $table->id();
        $table->timestamps();
    });
}

};
