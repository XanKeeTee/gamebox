<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Game $game)
    {
        // 1. Validamos que la nota sea obligatoria y del 1 al 5
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'nullable|string|max:1000',
        ]);

        // 2. Creamos la review asociada al usuario y al juego
        Review::create([
            'user_id' => Auth::id(),
            'game_id' => $game->id,
            'rating' => $request->rating,
            'content' => $request->content,
        ]);

        // 3. Volvemos atrás con un mensaje
        return back()->with('success', '¡Review publicada!');
    }
}