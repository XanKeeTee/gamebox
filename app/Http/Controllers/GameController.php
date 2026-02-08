<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User; // Importante
use App\Services\IgdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class GameController extends Controller
{
    protected $igdb;

    public function __construct(IgdbService $igdb)
    {
        $this->igdb = $igdb;
    }

    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $search = $request->input('q');

        $apiGames = $this->igdb->getGames($page, $search);

        $games = new LengthAwarePaginator(
            $apiGames,
            9999, 
            24,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $heroGame = $apiGames->first();
        $latestReviews = \App\Models\Review::with(['user', 'game'])->latest()->take(3)->get();
        $allGenres = collect();
        $allPlatforms = collect();

        return view('games.index', compact('games', 'heroGame', 'latestReviews', 'allGenres', 'allPlatforms'));
    }

    public function show($slug)
    {
        $game = Game::where('slug', $slug)
            ->with(['reviews.user', 'reviews.comments.user'])
            ->first();

        if (!$game) {
            $apiGame = $this->igdb->getGameBySlug($slug);
            if (!$apiGame) abort(404);

            $game = new Game();
            $game->forceFill((array)$apiGame);
            $game->exists = false; 
            $game->id = 0;         
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $userLists = $user ? $user->lists : collect();

        return view('games.show', compact('game', 'userLists'));
    }

    // Método Privado para guardar juego si no existe
    private function ensureGameExists($slug)
    {
        $game = Game::where('slug', $slug)->first();
        if ($game) return $game;

        $apiGame = $this->igdb->getGameBySlug($slug);
        if (!$apiGame) abort(404);

        return Game::create([
            'name' => $apiGame->name,
            'slug' => $apiGame->slug,
            'summary' => $apiGame->summary,
            'first_release_date' => $apiGame->first_release_date,
            'cover_url' => $apiGame->cover_url,
        ]);
    }

    // ACCIONES
    public function toggleLike(Request $request, $slug)
    {
        $game = $this->ensureGameExists($slug);
        
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $libraryEntry = $user->library()->where('game_id', $game->id)->first();

        if ($libraryEntry) {
            $user->library()->updateExistingPivot($game->id, ['liked' => !$libraryEntry->pivot->liked]);
        } else {
            $user->library()->attach($game->id, ['liked' => true]);
        }

        return back();
    }

    public function toggleWishlist(Request $request, $slug)
    {
        $game = $this->ensureGameExists($slug);
        
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $libraryEntry = $user->library()->where('game_id', $game->id)->first();

        if ($libraryEntry) {
            $user->library()->updateExistingPivot($game->id, ['wishlisted' => !$libraryEntry->pivot->wishlisted]);
        } else {
            $user->library()->attach($game->id, ['wishlisted' => true]);
        }

        return back();
    }

    public function updateStatus(Request $request, $slug)
    {
        $game = $this->ensureGameExists($slug);
        
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate(['status' => 'nullable|in:playing,completed,on_hold,dropped,backlog']);

        $libraryEntry = $user->library()->where('game_id', $game->id)->first();

        if ($libraryEntry) {
            $user->library()->updateExistingPivot($game->id, ['status' => $request->status]);
        } else {
            $user->library()->attach($game->id, ['status' => $request->status]);
        }

        return back()->with('message', 'Estado actualizado');
    }

    public function storeReview(Request $request, $slug)
    {
        $game = $this->ensureGameExists($slug);
        
        $request->validate([
            'content' => 'required|min:10|max:1000',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $existingReview = \App\Models\Review::where('user_id', Auth::id())
                                            ->where('game_id', $game->id)
                                            ->first();
        if ($existingReview) {
            return back()->with('error', 'Ya has reseñado este juego.');
        }

        $game->reviews()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'rating' => $request->rating,
        ]);

        return back()->with('message', 'Reseña publicada.');
    }

    public function updateJournal(Request $request, $slug)
    {
        $game = $this->ensureGameExists($slug);
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $data = [
            'hours_played' => $request->hours_played,
            'started_at' => $request->started_at,
            'finished_at' => $request->finished_at,
            'private_notes' => $request->private_notes,
        ];

        $libraryEntry = $user->library()->where('game_id', $game->id)->first();

        if ($libraryEntry) {
            $user->library()->updateExistingPivot($game->id, $data);
        } else {
            $user->library()->attach($game->id, $data);
        }

        return back()->with('message', 'Diario actualizado.');
    }
}