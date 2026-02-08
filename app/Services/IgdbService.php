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

        return $response->ok() ? $response->json() : [];
    }

    // --- MÉTODOS PÚBLICOS ---

    // 1. Catálogo Principal con Filtros
    public function getGames($page = 1, $search = null, $filters = [])
    {
        $limit = 24;
        $offset = ($page - 1) * $limit;
        $fields = "fields name, slug, cover.url, first_release_date, summary, total_rating, total_rating_count, genres.name, platforms.name;";
        
        // Construir cláusula WHERE
        $where = ["cover != null", "themes != (42)"]; // Excluir erotica/etc si se quiere
        
        if (!empty($filters['genre'])) {
            $where[] = "genres.slug = \"{$filters['genre']}\"";
        }
        
        if (!empty($filters['platform'])) {
            $where[] = "platforms.slug = \"{$filters['platform']}\"";
        }

        // Si hay búsqueda, IGDB ignora el 'sort' y usa relevancia
        if ($search) {
            $query = "{$fields} search \"{$search}\"; limit {$limit}; offset {$offset};";
            // Nota: IGDB no permite where complejos con search, es limitado.
        } else {
            // Filtros normales
            $whereStr = implode(' & ', $where);
            // Ordenación por defecto: populares
            $sort = "sort total_rating_count desc;";
            
            // Si filtramos por "recientes"
            if (isset($filters['sort']) && $filters['sort'] === 'newest') {
                $sort = "sort first_release_date desc;";
                $whereStr .= " & first_release_date != null & first_release_date < " . time();
            }

            $query = "{$fields} {$sort} where {$whereStr}; limit {$limit}; offset {$offset};";
        }

        return $this->formatGames($this->request('games', $query));
    }

    // 2. Juegos Populares (Para la Home)
    public function getPopularGames($limit = 6)
    {
        $query = "fields name, slug, cover.url, total_rating; sort total_rating_count desc; where total_rating > 70 & cover != null; limit {$limit};";
        return $this->formatGames($this->request('games', $query));
    }

    // 3. Nuevos Lanzamientos (Para la Home)
    public function getNewReleases($limit = 6)
    {
        $now = time();
        $query = "fields name, slug, cover.url, first_release_date; sort first_release_date desc; where first_release_date != null & first_release_date < {$now} & cover != null; limit {$limit};";
        return $this->formatGames($this->request('games', $query));
    }

    // 4. Próximamente (Para la Home)
    public function getUpcomingGames($limit = 6)
    {
        $now = time();
        $query = "fields name, slug, cover.url, first_release_date; sort first_release_date asc; where first_release_date > {$now} & cover != null; limit {$limit};";
        return $this->formatGames($this->request('games', $query));
    }

    // 5. Listas de Géneros y Plataformas (Para los filtros)
    public function getGenres() {
        // Cacheamos esto 24h porque no cambia mucho
        return Cache::remember('igdb_genres', 86400, function() {
            return $this->request('genres', "fields name, slug; limit 50; sort name asc;");
        });
    }

    public function getPlatforms() {
        return Cache::remember('igdb_platforms', 86400, function() {
            // Pedimos solo las plataformas principales (evitamos versiones raras)
            return $this->request('platforms', "fields name, slug; limit 50; sort name asc; where category = (1);"); 
        });
    }

    public function getGameBySlug($slug)
    {
        $query = "fields name, slug, summary, first_release_date, cover.url, genres.name, platforms.name, total_rating; where slug = \"{$slug}\";";
        $results = $this->request('games', $query);
        
        if (empty($results) || !isset($results[0])) return null;
        
        // Formateo manual porque es un solo objeto
        $game = $results[0];
        return (object) [
            'id' => 0, 
            'igdb_id' => $game['id'],
            'name' => $game['name'],
            'slug' => $game['slug'],
            'summary' => $game['summary'] ?? '',
            'cover_url' => isset($game['cover']) ? str_replace('t_thumb', 't_cover_big', $game['cover']['url']) : null,
            'first_release_date' => isset($game['first_release_date']) ? date('Y-m-d', $game['first_release_date']) : null,
            'rating' => $game['total_rating'] ?? 0,
            'exists' => false,
            'genres' => collect($game['genres'] ?? [])->pluck('name'),
            'platforms' => collect($game['platforms'] ?? [])->pluck('name'),
        ];
    }

    // Helper para formatear colecciones de juegos
    private function formatGames($results)
    {
        if (!is_array($results)) return collect();

        return collect($results)->map(function ($game) {
            return (object) [
                'id' => 0,
                'igdb_id' => $game['id'],
                'name' => $game['name'],
                'slug' => $game['slug'],
                'cover_url' => isset($game['cover']) ? str_replace('t_thumb', 't_cover_big', $game['cover']['url']) : null,
                'first_release_date' => $game['first_release_date'] ?? null,
                'rating' => $game['total_rating'] ?? 0,
                'exists' => false 
            ];
        });
    }
}