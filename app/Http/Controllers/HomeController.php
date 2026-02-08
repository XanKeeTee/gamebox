<?php

namespace App\Http\Controllers;

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
        // 1. Datos optimizados de IGDB (vienen de caché)
        $popularGames = $this->igdb->getPopularGames(6);
        $newReleases = $this->igdb->getNewReleases(6);
        $upcomingGames = $this->igdb->getUpcomingGames(6);

        // 2. Datos de la Comunidad (Eager Loading para optimizar SQL)
        $recentReviews = Review::with(['user', 'game'])
            ->latest()
            ->take(3)
            ->get();

        $heroGame = $popularGames->first();

        return view('home', compact('popularGames', 'newReleases', 'upcomingGames', 'recentReviews', 'heroGame'));
    }
}