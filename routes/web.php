<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReviewController; // Asegúrate de tener este controlador si usas reviews
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ReviewActionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirigir la página de inicio a la lista de juegos
Route::get('/', function () {
    return redirect()->route('games.index');
});

// RUTAS PÚBLICAS (Juegos y Comunidad)
// Las ponemos fuera del 'auth' para que cualquiera pueda verlas (aunque no esté logueado)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/games/{game:slug}', [GameController::class, 'show'])->name('games.show');
Route::get('/games', [GameController::class, 'index'])->name('games.index');

Route::get('/community', [UserController::class, 'index'])->name('users.index');
Route::get('/user/{name}', [UserController::class, 'show'])->name('users.show');


// RUTAS PRIVADAS (Requieren estar logueado)
Route::middleware('auth')->group(function () {

    // Perfil de Usuario (Laravel Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Biblioteca (Me gusta / Lista de deseos)
    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
    Route::post('/games/{game:slug}/like', [LibraryController::class, 'toggleLike'])->name('games.like');
    Route::post('/games/{game:slug}/wishlist', [LibraryController::class, 'toggleWishlist'])->name('games.wishlist');

    // Reviews (Publicar)
    // Nota: Si no tienes ReviewController creado, comenta esta línea para que no de error
    Route::post('/games/{game:slug}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    Route::post('/profile/favorite', [ProfileController::class, 'setFavorite'])->name('profile.setFavorite');

    Route::get('/api/search-games', [GameController::class, 'searchJson'])->name('games.searchJson');

    Route::post('/user/{user}/follow', [FollowController::class, 'toggle'])->name('users.follow');

    Route::post('/reviews/{review}/vote', [ReviewActionController::class, 'toggleVote'])->name('reviews.vote');
    Route::post('/reviews/{review}/comment', [ReviewActionController::class, 'storeComment'])->name('reviews.comment');

    Route::post('/games/{game}/status', [App\Http\Controllers\GameController::class, 'updateStatus'])->name('games.status');

    Route::post('/lists', [App\Http\Controllers\ListController::class, 'store'])->name('lists.store');
    Route::post('/games/{slug}/add-to-list', [App\Http\Controllers\ListController::class, 'addGame'])->name('lists.addGame');
    Route::get('/lists/{list}', [App\Http\Controllers\ListController::class, 'show'])->name('lists.show');

    Route::delete('/lists/{list}', [App\Http\Controllers\ListController::class, 'destroy'])->name('lists.destroy');
    Route::delete('/lists/{list}/game/{game}', [App\Http\Controllers\ListController::class, 'removeGame'])->name('lists.removeGame');

    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
});

require __DIR__ . '/auth.php';
