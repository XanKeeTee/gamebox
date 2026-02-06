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
        // 1. La tabla de las Listas (El contenedor)
        Schema::create('game_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Dueño de la lista
            $table->string('title'); // Título: "Mis Favoritos"
            $table->text('description')->nullable(); // Descripción opcional
            $table->boolean('is_public')->default(true); // Por si quieres listas privadas en el futuro
            $table->timestamps();
        });

        // 2. La tabla Pivote (Los juegos DENTRO de la lista)
        Schema::create('game_list_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_list_id')->constrained()->onDelete('cascade');
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0); // Para poder ordenar los juegos (Top 1, Top 2...)
            $table->timestamps();

            // Evitar duplicados: Un juego solo puede estar una vez en la misma lista
            $table->unique(['game_list_id', 'game_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_lists');
    }
};
