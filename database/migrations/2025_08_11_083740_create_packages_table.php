<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackagesTable extends Migration
{
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');               // Paket adı, məsələn: "Aylıq"
            $table->integer('duration_days');    // Paket müddəti günlərlə, məsələn: 30
            $table->decimal('price', 8, 2);      // Qiymət
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('packages');
    }
}
