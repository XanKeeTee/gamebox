<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\UserController;
// use App\Http\Controllers\ReviewController; // Ya no lo necesitamos aquí
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ReviewActionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirigir la página de inicio a la Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// RUTAS PÚBLICAS
Route::get('/games', [GameController::class, 'index'])->name('games.index');
Route::get('/games/{slug}', [GameController::class, 'show'])->name('games.show'); // Usamos {slug} para consistencia

Route::get('/community', [UserController::class, 'index'])->name('users.index');
Route::get('/user/{name}', [UserController::class, 'show'])->name('users.show');


// RUTAS PRIVADAS
Route::middleware('auth')->group(function () {

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Biblioteca
    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
    
    // Acciones Juego (Likes, Wishlist, Status, Journal)
    // Nota: Usamos {slug} en todas para que GameController maneje la creación
    Route::post('/games/{slug}/like', [GameController::class, 'toggleLike'])->name('games.like');
    Route::post('/games/{slug}/wishlist', [GameController::class, 'toggleWishlist'])->name('games.wishlist');
    Route::post('/games/{slug}/status', [GameController::class, 'updateStatus'])->name('games.status');
    Route::post('/games/{slug}/journal', [GameController::class, 'updateJournal'])->name('games.updateJournal');

    // REVIEWS - ESTA ERA LA LÍNEA QUE FALLABA
    // Ahora apunta a GameController::storeReview y usa {slug}
    Route::post('/games/{slug}/reviews', [GameController::class, 'storeReview'])->name('reviews.store');

    // Social y Perfil
    Route::post('/profile/favorite', [ProfileController::class, 'setFavorite'])->name('profile.setFavorite');
    Route::post('/user/{user}/follow', [FollowController::class, 'toggle'])->name('users.follow');

    // Acciones en Reviews
    Route::post('/reviews/{review}/vote', [ReviewActionController::class, 'toggleVote'])->name('reviews.vote');
    Route::post('/reviews/{review}/comment', [ReviewActionController::class, 'storeComment'])->name('reviews.comment');

    // Listas
    Route::post('/lists', [ListController::class, 'store'])->name('lists.store');
    Route::post('/games/{slug}/add-to-list', [ListController::class, 'addGame'])->name('lists.addGame');
    Route::get('/lists/{list}', [ListController::class, 'show'])->name('lists.show');
    Route::delete('/lists/{list}', [ListController::class, 'destroy'])->name('lists.destroy');
    Route::delete('/lists/{list}/game/{game}', [ListController::class, 'removeGame'])->name('lists.removeGame');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
});

require __DIR__ . '/auth.php';