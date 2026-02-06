<?php

namespace App\Models;

// 1. IMPORTARLO AQUÍ ARRIBA
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable; // <--- ¡ESTO ES CRUCIAL!

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    // 2. USARLO DENTRO DE LA CLASE
    use HasFactory, Notifiable; // <--- ¡TIENE QUE ESTAR AQUÍ TAMBIÉN!

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar', // Asegúrate de que esto esté si usas avatares
        'bio',    // Y esto si usas biografía
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // RELACIONES (Ya las tenías, las mantengo aquí para que no las pierdas)

    // Librería de Juegos
    public function library()
    {
        return $this->belongsToMany(Game::class)
                    ->withPivot([
                        'liked', 
                        'wishlisted', 
                        'favorite_slot', 
                        'status',
                        'hours_played',
                        'started_at', 
                        'finished_at', 
                        'private_notes'
                    ])
                    ->withTimestamps();
    }

    // Reviews escritas por el usuario
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Listas personalizadas
    public function lists()
    {
        return $this->hasMany(GameList::class)->latest();
    }

    // Seguidores
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'follower_id')->withTimestamps();
    }

    // Siguiendo
    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'following_id')->withTimestamps();
    }

    // Helper para saber si sigo a alguien
    public function isFollowing(User $user)
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }
}