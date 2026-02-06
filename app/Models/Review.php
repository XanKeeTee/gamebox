<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    // ESTO ES LO QUE TE FALTA:
    protected $fillable = [
        'user_id',
        'game_id',
        'rating',
        'content',
    ];

    // Relación: Una review pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación: Una review pertenece a un juego
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    // Relación con Votos (Likes/Dislikes)
    public function votes()
    {
        return $this->hasMany(ReviewVote::class);
    }

    // Relación con Comentarios
    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    // Helpers para contar votos
    public function likesCount() { return $this->votes()->where('is_like', true)->count(); }
    public function dislikesCount() { return $this->votes()->where('is_like', false)->count(); }

    // Helper para saber si YO ya voté
    public function userVote($userId)
    {
        return $this->votes()->where('user_id', $userId)->first();
    }
}