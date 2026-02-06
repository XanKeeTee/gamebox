<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $filter = $request->get('filter', 'liked'); // Por defecto 'liked'

        // Obtenemos los juegos de la biblioteca del usuario
        $games = $user->library()
            ->wherePivot($filter, true) // Filtramos donde liked=true o wishlisted=true
            ->orderByPivot('updated_at', 'desc') // Los últimos añadidos primero
            ->paginate(28);

        return view('library.index', compact('games', 'filter'));
    }
    
    public function toggleLike(Game $game)
    {
        $user = Auth::user();

        // Buscamos si ya existe el registro en la tabla intermedia
        $entry = $user->library()->where('game_id', $game->id)->first();

        if (!$entry) {
            // Si no existe, lo creamos con el like activado
            $user->library()->attach($game->id, ['liked' => true]);
        } else {
            // Si existe, invertimos el valor (true a false, o false a true)
            $user->library()->updateExistingPivot($game->id, [
                'liked' => !$entry->pivot->liked
            ]);
        }

        return back();
    }

    // Alternar "Lista de Deseos"
    public function toggleWishlist(Game $game)
    {
        $user = Auth::user();
        $entry = $user->library()->where('game_id', $game->id)->first();

        if (!$entry) {
            $user->library()->attach($game->id, ['wishlisted' => true]);
        } else {
            $user->library()->updateExistingPivot($game->id, [
                'wishlisted' => !$entry->pivot->wishlisted
            ]);
        }

        return back();
    }
}
