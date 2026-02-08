<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class IgdbService
{
    protected $clientId;
    protected $clientSecret;

    public function __construct()
    {
        $this->clientId = env('IGDB_CLIENT_ID');
        $this->clientSecret = env('IGDB_CLIENT_SECRET');
    }

    protected function getToken()
    {
        return Cache::remember('igdb_token', 3600, function () {
            $response = Http::withoutVerifying()->post('https://id.twitch.tv/oauth2/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
            ]);
            return $response->json()['access_token'] ?? null;
        });
    }

    protected function request($endpoint, $body)
    {
        $token = $this->getToken();
        if (!$token) return [];

        $response = Http::withoutVerifying()->withHeaders([
            'Client-ID' => $this->clientId,
            'Authorization' => 'Bearer ' . $token,
        ])
        ->withBody($body, 'text/plain')
        ->post("https://api.igdb.com/v4/{$endpoint}");

        return $response->successful() ? $response->json() : [];
    }

    // --- HELPER PARA BÚSQUEDAS Y FILTROS ---
    private function buildQueryBody($search = null, $filters = [], $isCount = false)
    {
        // AQUÍ ESTÁ LA CLAVE: "category = 0" elimina los DLCs
        $where = ["cover != null", "themes != (42)", "category = 0"];
        
        if (!empty($filters['genre'])) $where[] = "genres.slug = \"{$filters['genre']}\"";
        if (!empty($filters['platform'])) $where[] = "platforms.slug = \"{$filters['platform']}\"";

        $whereStr = implode(' & ', $where);

        if ($isCount) {
            if ($search) return "search \"{$search}\"; where {$whereStr};"; 
            return "where {$whereStr};";
        }

        $fields = "fields name, slug, cover.url, first_release_date, summary, total_rating, total_rating_count, genres.name, platforms.name;";
        
        if ($search) {
            return "{$fields} search \"{$search}\"; limit 24;";
        }

        $sort = "sort total_rating_count desc;";
        if (isset($filters['sort'])) {
            if ($filters['sort'] === 'newest') {
                $sort = "sort first_release_date desc;";
                $whereStr .= " & first_release_date != null & first_release_date < " . time();
            } elseif ($filters['sort'] === 'rating') {
                $sort = "sort total_rating desc;";
                $whereStr .= " & total_rating_count > 10";
            }
        }

        return "{$fields} {$sort} where {$whereStr}; limit 24;";
    }

    // --- MÉTODOS PÚBLICOS ---

    public function getGames($page = 1, $search = null, $filters = [])
    {
        $cacheKey = 'games_index_' . $page . '_' . md5($search . json_encode($filters));

        return Cache::remember($cacheKey, 600, function () use ($page, $search, $filters) {
            $offset = ($page - 1) * 24;
            $queryBody = $this->buildQueryBody($search, $filters) . " offset {$offset};";
            return $this->formatGames($this->request('games', $queryBody));
        });
    }

    public function countGames($search = null, $filters = [])
    {
        $cacheKey = 'games_count_' . md5($search . json_encode($filters));

        return Cache::remember($cacheKey, 3600, function () use ($search, $filters) {
            $queryBody = $this->buildQueryBody($search, $filters, true);
            $response = $this->request('games/count', $queryBody);
            return $response['count'] ?? 0;
        });
    }

    public function getPopularGames($limit = 6)
    {
        return Cache::remember('home_popular', 3600, function () use ($limit) {
            // Añadido "& category = 0"
            $query = "fields name, slug, cover.url, total_rating; sort total_rating_count desc; where total_rating > 70 & cover != null & category = 0; limit {$limit};";
            return $this->formatGames($this->request('games', $query));
        });
    }

    public function getNewReleases($limit = 6)
    {
        return Cache::remember('home_new', 3600, function () use ($limit) {
            $now = time();
            // Añadido "& category = 0"
            $query = "fields name, slug, cover.url, first_release_date; sort first_release_date desc; where first_release_date != null & first_release_date < {$now} & cover != null & category = 0; limit {$limit};";
            return $this->formatGames($this->request('games', $query));
        });
    }

    public function getUpcomingGames($limit = 6)
    {
        return Cache::remember('home_upcoming', 3600, function () use ($limit) {
            $now = time();
            // Añadido "& category = 0"
            $query = "fields name, slug, cover.url, first_release_date; sort first_release_date asc; where first_release_date > {$now} & cover != null & category = 0; limit {$limit};";
            return $this->formatGames($this->request('games', $query));
        });
    }

    public function getGenres() {
        return Cache::remember('igdb_genres', 86400, function() {
            return $this->request('genres', "fields name, slug; limit 50; sort name asc;");
        });
    }

    public function getPlatforms() {
        return Cache::remember('igdb_platforms', 86400, function() {
            return $this->request('platforms', "fields name, slug; limit 50; sort name asc; where category = (1);"); 
        });
    }

    public function getGameBySlug($slug)
    {
        return Cache::remember("game_details_{$slug}", 3600, function () use ($slug) {
            $query = "fields name, slug, summary, first_release_date, cover.url, genres.name, platforms.name, total_rating; where slug = \"{$slug}\";";
            $results = $this->request('games', $query);
            
            if (empty($results) || !isset($results[0])) return null;
            
            $game = $results[0];
            return (object) [
                'id' => 0, 
                'igdb_id' => $game['id'],
                'name' => $game['name'],
                'slug' => $game['slug'],
                'summary' => $game['summary'] ?? '',
                'cover_url' => isset($game['cover']) ? "https:" . str_replace('t_thumb', 't_cover_big', $game['cover']['url']) : null, // Fix HTTPS
                'first_release_date' => isset($game['first_release_date']) ? date('Y-m-d', $game['first_release_date']) : null,
                'rating' => $game['total_rating'] ?? 0,
                'exists' => false,
                'genres' => collect($game['genres'] ?? [])->pluck('name'),
                'platforms' => collect($game['platforms'] ?? [])->pluck('name'),
            ];
        });
    }

    private function formatGames($results)
    {
        if (!is_array($results)) return collect();
        return collect($results)->map(function ($game) {
            return (object) [
                'id' => 0,
                'igdb_id' => $game['id'],
                'name' => $game['name'],
                'slug' => $game['slug'],
                // FIX CRÍTICO: Añadir 'https:' para que se vea la imagen
                'cover_url' => isset($game['cover']) ? "https:" . str_replace('t_thumb', 't_cover_big', $game['cover']['url']) : null,
                'first_release_date' => $game['first_release_date'] ?? null,
                'rating' => $game['total_rating'] ?? 0,
                'exists' => false 
            ];
        });
    }
}