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

    // Obtener Token (con withoutVerifying para XAMPP)
    protected function getToken()
    {
        return Cache::remember('igdb_token', 3600, function () {
            
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withoutVerifying()->post('https://id.twitch.tv/oauth2/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
            ]);

            if ($response->failed()) {
                // Usamos logger() para que no rompa la ejecución si falla
                logger()->error('Error obteniendo token IGDB: ' . $response->body());
                return null;
            }

            return $response->json()['access_token'] ?? null;
        });
    }

    // Método base para peticiones (con withoutVerifying para XAMPP)
    protected function request($endpoint, $body)
    {
        $token = $this->getToken();

        if (!$token) {
            return [];
        }

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withoutVerifying()->withHeaders([
            'Client-ID' => $this->clientId,
            'Authorization' => 'Bearer ' . $token,
        ])
        ->withBody($body, 'text/plain')
        ->post("https://api.igdb.com/v4/{$endpoint}");

        // Verificamos si la respuesta es exitosa antes de convertir a JSON
        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    // Listado para el Index
    public function getGames($page = 1, $search = null)
    {
        $limit = 24;
        $offset = ($page - 1) * $limit;
        $fields = "fields name, slug, cover.url, first_release_date, summary, total_rating, total_rating_count;";
        
        if ($search) {
            $query = "{$fields} search \"{$search}\"; limit {$limit}; offset {$offset};";
        } else {
            $query = "{$fields} sort total_rating_count desc; where total_rating > 60 & cover != null; limit {$limit}; offset {$offset};";
        }

        $results = $this->request('games', $query);

        if (!is_array($results)) return collect();

        return collect($results)->map(function ($game) {
            return (object) [
                'id' => 0, // ID 0 indica que viene de API
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

    // Juego individual por Slug
    public function getGameBySlug($slug)
    {
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
            'cover_url' => isset($game['cover']) ? str_replace('t_thumb', 't_cover_big', $game['cover']['url']) : null,
            'first_release_date' => isset($game['first_release_date']) ? date('Y-m-d H:i:s', $game['first_release_date']) : null,
            'rating' => $game['total_rating'] ?? 0,
            'exists' => false,
            'genres' => collect($game['genres'] ?? [])->pluck('name'),
            'platforms' => collect($game['platforms'] ?? [])->pluck('name'),
        ];
    }
}