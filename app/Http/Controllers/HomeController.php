<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\IgdbService;
use App\Models\Review;

class HomeController extends Controller
{
    protected $igdb;

    public function __construct(IgdbService $igdb)
    {
        $this->igdb = $igdb;
    }

    public function index()
    {
        // 1. Datos de IGDB
        $popularGames = $this->igdb->getPopularGames(6);
        $newReleases = $this->igdb->getNewReleases(6);
        $upcomingGames = $this->igdb->getUpcomingGames(6);

        // 2. Datos Locales (Comunidad)
        // Reseñas recientes de usuarios de tu web
        $recentReviews = Review::with(['user', 'game'])
            ->latest()
            ->take(4)
            ->get();

        // Juego destacado (Hero) - Usamos el primero de populares
        $heroGame = $popularGames->first();

        return view('home', compact('popularGames', 'newReleases', 'upcomingGames', 'recentReviews', 'heroGame'));
    }
}