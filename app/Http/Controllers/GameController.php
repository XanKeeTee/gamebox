<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class GameController extends Controller
{
    // 1. Catálogo Principal + Buscador Mixto
    public function index(Request $request)
    {
        $query = Game::query();
        $users = collect();

        // 1. Lógica del Buscador (Igual que antes)
        if ($request->has('q') && $request->q != '') {
            $query->where('name', 'LIKE', '%' . $request->q . '%');
            $users = User::where('name', 'LIKE', '%' . $request->q . '%')->take(6)->get();
            $games = $query->orderBy('first_release_date', 'desc')->paginate(28);
        } else {
            // Si NO busca, mostramos aleatorios
            $games = $query->inRandomOrder()->paginate(28);
        }

        // 2. DATOS EXTRA PARA LA PORTADA (NUEVO)
        // A. Hero Game: Un juego aleatorio que tenga portada para el banner gigante
        $heroGame = Game::whereNotNull('cover_url')
            ->inRandomOrder()
            ->first();

        // B. Últimas Reviews: Las 3 más recientes de toda la web con sus usuarios y juegos
        $latestReviews = \App\Models\Review::with(['user', 'game'])
            ->latest()
            ->take(3)
            ->get();

        $games->appends(['q' => $request->q]);

        if ($request->ajax()) {
            return view('games.partials.grid', compact('games', 'users'))->render();
        }

        // Pasamos todo a la vista
        return view('games.index', compact('games', 'users', 'heroGame', 'latestReviews'));
    }
    // 2. Ficha del Juego
    public function show($slug)
    {
        // 1. Buscamos el juego (con sus relaciones)
        $game = Game::where('slug', $slug)
            ->with(['reviews.user', 'reviews.comments.user']) // Cargamos reviews y comentarios
            ->firstOrFail();

        // 2. NUEVO: Si el usuario está conectado, traemos sus listas para el desplegable
        $userLists = Auth::check() ? Auth::user()->lists : collect();

        // 3. Retornamos la vista pasando también $userLists
        return view('games.show', compact('game', 'userLists'));
    }

    // 3. BÚSQUEDA RÁPIDA JSON (Para el Modal del Perfil)
    public function searchJson(Request $request)
    {
        $query = $request->get('q');

        // Si escribe menos de 2 letras, devolvemos array vacío
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        // Buscamos juegos y devolvemos JSON
        $games = Game::where('name', 'LIKE', '%' . $query . '%')
            ->take(10)
            ->get(['id', 'name', 'cover_url', 'first_release_date']);

        return response()->json($games);
    }

    // Método para actualizar el estado (Playing, Completed, etc.)
    public function updateStatus(Request $request, $slug)
    {
        $game = Game::where('slug', $slug)->firstOrFail();
        $user = Auth::user();

        // Validamos que el estado sea uno de los permitidos
        $request->validate([
            'status' => 'nullable|in:playing,completed,on_hold,dropped,backlog'
        ]);

        // Sincronizamos sin borrar lo demás (syncWithoutDetaching no sirve bien aquí para actualizar pivot)
        // Usaremos updateExistingPivot o attach si no existe.

        // 1. Comprobar si ya tiene el juego en libreria
        $libraryEntry = $user->library()->where('game_id', $game->id)->first();

        if ($libraryEntry) {
            // Si ya existe, actualizamos solo el status
            $user->library()->updateExistingPivot($game->id, ['status' => $request->status]);
        } else {
            // Si no lo tenía, lo añadimos nuevo con ese status
            $user->library()->attach($game->id, ['status' => $request->status]);
        }

        return back()->with('message', 'Estado actualizado');
    }
}
