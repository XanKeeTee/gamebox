<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameList extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'description', 'is_public'];

    // Una lista pertenece a un Usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Una lista tiene muchos Juegos
    public function games()
    {
        return $this->belongsToMany(Game::class, 'game_list_entries')
                    ->withPivot('order')
                    ->withTimestamps()
                    ->orderByPivot('order', 'asc'); // Siempre saldrán ordenados
    }
}