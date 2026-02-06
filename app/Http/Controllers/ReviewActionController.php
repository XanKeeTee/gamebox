<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Comment;
use App\Models\ReviewVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\GameBoxNotification;

class ReviewActionController extends Controller
{
    // 1. Manejar Voto (Like/Dislike)
    public function toggleVote(Request $request, Review $review)
    {
        $user = Auth::user();
        $isLike = $request->boolean('is_like'); // Recibe true o false

        // Buscamos si ya existe un voto de este usuario en esta reseña
        $existingVote = ReviewVote::where('user_id', $user->id)
            ->where('review_id', $review->id)
            ->first();

        if ($existingVote) {
            // Si ya votó...
            if ($existingVote->is_like == $isLike) {
                // Si le da click a lo mismo (ej: Like sobre Like) -> BORRAR VOTO
                $existingVote->delete();
            } else {
                // Si cambia de opinión (de Like a Dislike) -> ACTUALIZAR
                $existingVote->update(['is_like' => $isLike]);
            }
        } else {
            // Si no había voto -> CREAR UNO NUEVO
            ReviewVote::create([
                'user_id' => $user->id,
                'review_id' => $review->id,
                'is_like' => $isLike
            ]);
        }

        if ($isLike && $review->user_id !== Auth::id()) {
            $review->user->notify(new GameBoxNotification(
                'like',
                Auth::user()->name . ' le gustó tu reseña de ' . $review->game->name,
                route('games.show', $review->game->slug),
                Auth::user()
            ));
        }
        return back(); // Recargar página
    }

    // 2. Guardar Comentario
    public function storeComment(Request $request, Review $review)
    {
        $request->validate(['content' => 'required|max:500']);

        Comment::create([
            'user_id' => Auth::id(),
            'review_id' => $review->id,
            'content' => $request->content
        ]);
        
        if ($review->user_id !== Auth::id()) {
            $review->user->notify(new GameBoxNotification(
                'comment',
                Auth::user()->name . ' comentó tu reseña de ' . $review->game->name,
                route('games.show', $review->game->slug) . '#comments', // Añadimos ancla
                Auth::user()
            ));
        }
        return back();
    }
}
