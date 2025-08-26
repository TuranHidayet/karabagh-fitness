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
         Schema::table('packages', function (Blueprint $table) {
            $table->time('shift_start')->nullable()->after('price');
            $table->time('shift_end')->nullable();
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->time('shift_start')->nullable()->after('price');
            $table->time('shift_end')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['shift_start', 'shift_end']);
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['shift_start', 'shift_end']);
        });
    }
};
