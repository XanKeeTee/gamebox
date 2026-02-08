<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\GameBoxNotification;
use App\Services\IgdbService; // Importamos el servicio

class UserController extends Controller
{
    // Inyectamos el servicio
    protected $igdb;
    public function __construct(IgdbService $igdb) {
        $this->igdb = $igdb;
    }

    public function show(Request $request, $name)
    {
        $user = User::where('name', $name)->firstOrFail();
        $tab = $request->query('tab', 'profile');

        $stats = [
            'followers' => $user->followers()->count(),
            'following' => $user->following()->count(),
            'total_reviews' => $user->reviews()->count(),
            'total_liked' => $user->library()->wherePivot('liked', true)->count(),
            'total_wish' => $user->library()->wherePivot('wishlisted', true)->count(),
        ];

        $viewData = [
            'user' => $user,
            'tab' => $tab,
            'stats' => $stats,
            'favorites' => collect(),
            'games_list' => collect(),
            'reviews_list' => collect(),
            'users_list' => collect(),
            'lists' => collect(),
        ];

        if ($tab === 'profile') {
            $viewData['favorites'] = $user->library()
                ->wherePivotNotNull('favorite_slot')
                ->orderByPivot('favorite_slot')
                ->get();

        } elseif ($tab === 'games') {
            $viewData['games_list'] = $user->library()
                ->withPivot(['liked', 'wishlisted'])
                ->orderByPivot('created_at', 'desc')
                ->paginate(24);

        } elseif ($tab === 'lists') {
            $viewData['lists'] = $user->lists()
                ->with(['games' => function($q) { $q->take(4); }])
                ->latest()
                ->paginate(12);

        } elseif ($tab === 'reviews') {
            $viewData['reviews_list'] = $user->reviews()
                ->with('game')
                ->latest()
                ->paginate(10);

        } elseif ($tab === 'followers') {
            $viewData['users_list'] = $user->followers()->paginate(20);

        } elseif ($tab === 'following') {
            $viewData['users_list'] = $user->following()->paginate(20);
        }

        return view('users.show', $viewData);
    }

    public function toggleFollow(User $user)
    {
        /** @var \App\Models\User $me */
        $me = Auth::user();
        if ($me->id === $user->id) return back();

        if ($me->following()->where('followed_id', $user->id)->exists()) {
            $me->following()->detach($user->id);
        } else {
            $me->following()->attach($user->id);
            $user->notify(new GameBoxNotification(
                'follow',
                $me->name . ' empezó a seguirte',
                route('users.show', $me->name),
                $me
            ));
        }
        return back();
    }

    // --- ACTUALIZADO: AHORA CREA EL JUEGO SI VIENE DE LA API ---
    public function setFavorite(Request $request)
    {
        $request->validate([
            'slug' => 'required|string', // Validamos slug en vez de ID
            'slot' => 'required|integer|min:1|max:5',
        ]);

        $user = Auth::user();
        $slug = $request->slug;
        $slot = $request->slot;

        // 1. Buscar o Crear el juego en BD
        $game = Game::where('slug', $slug)->first();
        if (!$game) {
            $apiGame = $this->igdb->getGameBySlug($slug);
            if (!$apiGame) return back()->with('error', 'Juego no encontrado.');
            
            $game = Game::updateOrCreate(
                ['igdb_id' => $apiGame->igdb_id],
                [
                    'name' => $apiGame->name,
                    'slug' => $apiGame->slug,
                    'summary' => $apiGame->summary,
                    'first_release_date' => $apiGame->first_release_date,
                    'cover_url' => $apiGame->cover_url,
                    'igdb_id' => $apiGame->igdb_id
                ]
            );
        }

        // 2. Limpiar el slot anterior
        /** @var \App\Models\User $user */
        $existingInSlot = $user->library()->wherePivot('favorite_slot', $slot)->pluck('game_id');
        if ($existingInSlot->isNotEmpty()) {
            $user->library()->updateExistingPivot($existingInSlot, ['favorite_slot' => null]);
        }

        // 3. Añadir a biblioteca si no estaba
        if (!$user->library()->where('game_id', $game->id)->exists()) {
            $user->library()->attach($game->id);
        }

        // 4. Asignar slot
        $user->library()->updateExistingPivot($game->id, ['favorite_slot' => $slot]);

        return back()->with('message', 'Favoritos actualizados.');
    }
}