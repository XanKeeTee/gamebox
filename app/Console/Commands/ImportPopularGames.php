<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MarcReichel\IGDBLaravel\Models\Game as IGDBGame;
use App\Models\Game;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\DB; // <--- IMPORTANTE: Necesario para reconectar

class ImportPopularGames extends Command
{
    protected $signature = 'import:popular-games';
    protected $description = 'Importa 500 juegos y gestiona la conexión a la BD';

    public function handle()
    {
        $this->info('Conectando con IGDB...');

        // 1. Configuramos el traductor
        $tr = new GoogleTranslate('es'); 
        $tr->setSource('en');

        // 2. Pedimos los juegos (Lote de 500)
        $igdbGames = IGDBGame::with(['cover'])
            ->where('total_rating_count', '>', 20)
            ->orderBy('total_rating_count', 'desc')
            ->limit(500) 
            ->get();

        $count = count($igdbGames);
        $this->info("¡Encontrados {$count} juegos! Iniciando proceso blindado...");
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($igdbGames as $index => $g) {
            $url = null;
            $summaryES = "Sin descripción.";

            // A. Procesar Imagen
            if (isset($g->cover) && isset($g->cover['url'])) {
                $url = $g->cover['url'];
                if (str_starts_with($url, '//')) $url = 'https:' . $url;
                $url = str_replace('t_thumb', 't_cover_big', $url);
            }

            // B. Traducir (Con manejo de errores)
            if (!empty($g->summary)) {
                try {
                    $summaryES = $tr->translate($g->summary);
                } catch (\Exception $e) {
                    $summaryES = $g->summary; // Si falla, guardamos en inglés
                }
            }

            // C. EL FIX MAESTRO: Reconectar si la base de datos se ha ido
            try {
                // Hacemos un "ping" a la base de datos
                DB::connection()->getPdo();
            } catch (\Exception $e) {
                // Si falla, reconectamos forzosamente
                DB::reconnect();
            }

            // D. Guardar
            Game::updateOrCreate(
                ['igdb_id' => $g->id],
                [
                    'name'               => $g->name,
                    'slug'               => $g->slug ?? str($g->name)->slug(),
                    'summary'            => $summaryES,
                    'cover_url'          => $url,
                    'first_release_date' => $g->first_release_date ?? null,
                ]
            );

            // E. Pausa para no saturar Google (ajustada)
            usleep(100000); // 0.1 segundos

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('¡Misión cumplida! Base de datos actualizada.');
    }
}