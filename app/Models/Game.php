<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'igdb_id',
        'name',
        'slug',
        'summary',
        'cover_url',
        'first_release_date'
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class)->latest(); // Las más nuevas primero
    }

    // Relación: Usuarios que tienen este juego en su biblioteca
    // En app/Models/Game.php

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('liked', 'wishlisted', 'favorite_slot') // <--- ¡AÑADE ESTO!
            ->withTimestamps();
    }

    public function lists()
    {
        return $this->belongsToMany(GameList::class, 'game_list_entries');
    }
}
