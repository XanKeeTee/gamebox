<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\GameBoxNotification; // Importante para las notificaciones

class UserController extends Controller
{
    /**
     * Muestra el perfil del usuario con sus pestañas.
     */
    public function show(Request $request, $name)
    {
        // 1. Buscar usuario por nombre
        $user = User::where('name', $name)->firstOrFail();
        
        // 2. Determinar qué pestaña estamos viendo (por defecto 'profile')
        $tab = $request->query('tab', 'profile');

        // 3. Datos comunes (Estadísticas del header)
        $stats = [
            'followers' => $user->followers()->count(),
            'following' => $user->following()->count(),
            'total_reviews' => $user->reviews()->count(),
            'total_liked' => $user->library()->wherePivot('liked', true)->count(),
            'total_wish' => $user->library()->wherePivot('wishlisted', true)->count(),
        ];

        // 4. Variables dinámicas según la pestaña
        $viewData = [
            'user' => $user,
            'tab' => $tab,
            'stats' => $stats,
            'favorites' => collect(),   // Para tab profile
            'games_list' => collect(),  // Para tab games
            'reviews_list' => collect(),// Para tab reviews
            'users_list' => collect(),  // Para tab followers/following
            'lists' => collect(),       // Para tab lists
        ];

        // 5. Lógica del Switch de Pestañas
        if ($tab === 'profile') {
            // Obtenemos los juegos marcados como favoritos (Slots 1 al 5)
            $viewData['favorites'] = $user->library()
                ->wherePivotNotNull('favorite_slot')
                ->orderByPivot('favorite_slot')
                ->get();

        } elseif ($tab === 'games') {
            // Librería completa del usuario
            $viewData['games_list'] = $user->library()
                ->withPivot(['liked', 'wishlisted']) // Cargar estado para los iconitos
                ->orderByPivot('created_at', 'desc')
                ->paginate(24);

        } elseif ($tab === 'lists') {
            // === NUEVO: LISTAS PERSONALIZADAS ===
            // Traemos las listas y pre-cargamos solo los primeros 4 juegos para la miniatura
            $viewData['lists'] = $user->lists()
                ->with(['games' => function($q) {
                    $q->take(4); 
                }])
                ->latest()
                ->paginate(12);

        } elseif ($tab === 'reviews') {
            // Reviews del usuario
            $viewData['reviews_list'] = $user->reviews()
                ->with('game') // Cargar juego para mostrar portada
                ->latest()
                ->paginate(10);

        } elseif ($tab === 'followers') {
            $viewData['users_list'] = $user->followers()->paginate(20);

        } elseif ($tab === 'following') {
            $viewData['users_list'] = $user->following()->paginate(20);
        }

        return view('users.show', $viewData);
    }

    /**
     * Acción de Seguir / Dejar de seguir.
     */
    public function toggleFollow(User $user)
    {
        /** @var \App\Models\User $me */
        $me = Auth::user();

        // No puedes seguirte a ti mismo
        if ($me->id === $user->id) {
            return back();
        }

        // Si ya lo sigo -> Dejar de seguir (Detach)
        if ($me->isFollowing($user)) {
            $me->following()->detach($user->id);
        } 
        // Si no lo sigo -> Seguir (Attach) y Notificar
        else {
            $me->following()->attach($user->id);

            // === NOTIFICACIÓN ===
            $user->notify(new GameBoxNotification(
                'follow', // Tipo
                $me->name . ' empezó a seguirte', // Mensaje
                route('users.show', $me->name), // URL
                $me // Usuario que origina la acción
            ));
        }

        return back();
    }

    /**
     * Guardar un juego en el Top 5 (Favoritos).
     */
    public function setFavorite(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'slot' => 'required|integer|min:1|max:5',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $gameId = $request->game_id;
        $slot = $request->slot;

        // 1. Limpiar el slot si ya estaba ocupado por otro juego
        // (Buscamos si hay algún juego en ese slot y lo ponemos a null)
        $user->library()->wherePivot('favorite_slot', $slot)->get()->each->pivot->update(
            $user->library()->wherePivot('favorite_slot', $slot)->pluck('game_id'),
            ['favorite_slot' => null]
        );

        // 2. Si el juego nuevo no estaba en la librería, añadirlo primero
        if (!$user->library()->where('game_id', $gameId)->exists()) {
            $user->library()->attach($gameId);
        }

        // 3. Asignar el slot al nuevo juego
        $user->library()->updateExistingPivot($gameId, ['favorite_slot' => $slot]);

        return back()->with('message', 'Favoritos actualizados.');
    }
}