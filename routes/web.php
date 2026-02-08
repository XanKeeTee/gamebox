<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ReviewActionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirigir inicio
Route::get('/', [HomeController::class, 'index'])->name('home');

// RUTAS PÚBLICAS
Route::get('/games', [GameController::class, 'index'])->name('games.index');
Route::get('/games/{slug}', [GameController::class, 'show'])->name('games.show'); // CORREGIDO: {slug}

Route::get('/community', [UserController::class, 'index'])->name('users.index');
Route::get('/user/{name}', [UserController::class, 'show'])->name('users.show');

// RUTAS PRIVADAS
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
    
    // --- AQUÍ ESTABA EL ERROR ---
    // Hemos cambiado {game:slug} por {slug} y apuntado todo a GameController
    Route::post('/games/{slug}/like', [GameController::class, 'toggleLike'])->name('games.like');
    Route::post('/games/{slug}/wishlist', [GameController::class, 'toggleWishlist'])->name('games.wishlist');
    Route::post('/games/{slug}/status', [GameController::class, 'updateStatus'])->name('games.status');
    Route::post('/games/{slug}/journal', [GameController::class, 'updateJournal'])->name('games.updateJournal');
    
    // Ruta de Reviews arreglada:
    Route::post('/games/{slug}/reviews', [GameController::class, 'storeReview'])->name('reviews.store');

    Route::post('/profile/favorite', [ProfileController::class, 'setFavorite'])->name('profile.setFavorite');
    Route::post('/user/{user}/follow', [FollowController::class, 'toggle'])->name('users.follow');
    Route::post('/reviews/{review}/vote', [ReviewActionController::class, 'toggleVote'])->name('reviews.vote');
    Route::post('/reviews/{review}/comment', [ReviewActionController::class, 'storeComment'])->name('reviews.comment');
    
    Route::post('/lists', [ListController::class, 'store'])->name('lists.store');
    Route::post('/games/{slug}/add-to-list', [ListController::class, 'addGame'])->name('lists.addGame');
    Route::get('/lists/{list}', [ListController::class, 'show'])->name('lists.show');
    Route::delete('/lists/{list}', [ListController::class, 'destroy'])->name('lists.destroy');
    Route::delete('/lists/{list}/game/{game}', [ListController::class, 'removeGame'])->name('lists.removeGame');
    
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
});

require __DIR__ . '/auth.php';