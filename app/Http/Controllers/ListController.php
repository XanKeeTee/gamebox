<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListController extends Controller
{
    // 1. Crear una Nueva Lista
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:50',
            'description' => 'nullable|max:200',
        ]);

        GameList::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'is_public' => true, // Por defecto públicas
        ]);

        return back()->with('message', 'Lista creada con éxito.');
    }

    // 2. Añadir un Juego a una Lista existente
    public function addGame(Request $request, $slug)
    {
        // 1. Buscamos el juego manualmente por su SLUG
        $game = Game::where('slug', $slug)->firstOrFail();

        $request->validate([
            'list_id' => 'required|exists:game_lists,id',
        ]);

        $list = GameList::findOrFail($request->list_id);

        // ... (El resto del código sigue igual)
        if ($list->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$list->games()->where('game_id', $game->id)->exists()) {
            $list->games()->attach($game->id);
            return back()->with('message', 'Juego añadido a la lista.');
        }

        return back()->with('error', 'El juego ya estaba en esa lista.');
    }

    // 3. Ver una lista (Lo usaremos más adelante)
    public function show(GameList $list)
    {
        $games = $list->games()->paginate(20);
        return view('lists.show', compact('list', 'games'));
    }

    // Borrar la lista entera
    public function destroy(GameList $list)
    {
        if ($list->user_id !== Auth::id()) abort(403);

        $list->delete();
        return redirect()->route('users.show', ['name' => Auth::user()->name, 'tab' => 'lists'])
            ->with('message', 'Lista eliminada.');
    }

    // Quitar un juego de la lista
    public function removeGame(GameList $list, Game $game)
    {
        if ($list->user_id !== Auth::id()) abort(403);

        $list->games()->detach($game->id);

        return back()->with('message', 'Juego eliminado de la lista.');
    }
}
