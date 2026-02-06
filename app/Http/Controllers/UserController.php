<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Buscador de usuarios (Página dedicada)
    public function index(Request $request)
    {
        $query = User::query();
        if ($search = $request->input('q')) {
            $query->where('name', 'LIKE', "%{$search}%");
        }
        $users = $query->orderBy('created_at', 'desc')->paginate(24);
        return view('users.index', compact('users'));
    }

    // Perfil Público de un usuario
    public function show(Request $request, $name)
    {
        $user = User::where('name', $name)->firstOrFail();
        $tab = $request->get('tab', 'profile'); // Por defecto 'profile'

        // Datos comunes (Stats)
        $stats = [
            'total_reviews' => $user->reviews()->count(),
            'total_liked'   => $user->library()->wherePivot('liked', true)->count(),
            'total_wish'    => $user->library()->wherePivot('wishlisted', true)->count(),
            'followers'     => $user->followers()->count(),
            'following'     => $user->following()->count(),
        ];

        // Preparamos los datos básicos para la vista
        $viewData = [
            'user' => $user,
            'stats' => $stats,
            'tab' => $tab
        ];

        // --- LÓGICA DE PESTAÑAS ---

        if ($tab === 'games') {
            // Pestaña JUEGOS
            $viewData['games_list'] = $user->library()->orderByPivot('updated_at', 'desc')->paginate(24);
        } elseif ($tab === 'reviews') {
            // Pestaña REVIEWS
            $viewData['reviews_list'] = $user->reviews()->with('game')->latest()->paginate(15);
        } elseif ($tab === 'followers') {
            // Pestaña SEGUIDORES (NUEVO)
            $viewData['users_list'] = $user->followers()->paginate(20);
        } elseif ($tab === 'following') {
            // Pestaña SIGUIENDO (NUEVO)
            $viewData['users_list'] = $user->following()->paginate(20);
        } elseif ($tab === 'following') {
            $viewData['users_list'] = $user->following()->paginate(20);
        } elseif ($tab === 'lists') {
            // Traemos las listas con los primeros 4 juegos para hacer una previsualización de portadas
            $viewData['lists'] = $user->lists()->with(['games' => function ($q) {
                $q->take(4); // Solo necesitamos los primeros 4 para la "miniaturita"
            }])->paginate(12);
        } else {
            // Pestaña PERFIL (Default)
            $viewData['favorites'] = $user->library()
                ->whereNotNull('favorite_slot')
                ->orderBy('favorite_slot')
                ->get();
        }

        return view('users.show', $viewData);
    }
}
