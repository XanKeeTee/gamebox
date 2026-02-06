<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
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

    // Añade esta función dentro de la clase User
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Relación: Biblioteca del usuario (Likes y Wishlist)
    // En app/Models/User.php

    public function library()
    {
        // Asegúrate de añadir 'status' aquí
        return $this->belongsToMany(Game::class)
            ->withPivot('liked', 'wishlisted', 'favorite_slot', 'status')
            ->withTimestamps();
    }

    // Usuarios que ME siguen a mí
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'follower_id')->withTimestamps();
    }

    // Usuarios a los que YO sigo
    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'following_id')->withTimestamps();
    }

    // Helper para saber si ya sigo a alguien
    public function isFollowing(User $user)
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    public function lists()
    {
        return $this->hasMany(GameList::class)->latest();
    }

}
