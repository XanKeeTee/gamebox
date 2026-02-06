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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('igdb_id')->unique(); // ID original de IGDB
            $table->string('name');
            $table->string('slug')->unique(); // Para URLs bonitas: gamebox.com/games/elden-ring
            $table->text('summary')->nullable();
            $table->string('cover_url')->nullable();
            $table->date('first_release_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
