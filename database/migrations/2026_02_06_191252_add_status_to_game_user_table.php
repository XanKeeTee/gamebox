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
        Schema::table('game_user', function (Blueprint $table) {
            // Añadimos la columna 'status' que puede ser nula al principio
            $table->string('status')->nullable()->after('wishlisted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_user', function (Blueprint $table) {
            //
        });
    }
};
