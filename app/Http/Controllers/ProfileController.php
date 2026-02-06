<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // LÓGICA DE LA FOTO DE PERFIL
        if ($request->hasFile('avatar')) {
            // 1. Borrar foto anterior si existe
            if ($request->user()->avatar) {
                Storage::disk('public')->delete($request->user()->avatar);
            }

            // 2. Guardar la nueva
            $path = $request->file('avatar')->store('avatars', 'public');
            $request->user()->avatar = $path;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function setFavorite(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'slot'    => 'required|integer|min:1|max:5',
        ]);

        $user = $request->user();
        $slot = $request->input('slot');
        $gameId = $request->input('game_id');

        // 1. Limpiar el hueco si ya había otro juego ahí
        // (Buscamos si el usuario ya tenía algo en el slot X y se lo quitamos)
        $user->library()->wherePivot('favorite_slot', $slot)->updateExistingPivot(
            $user->library()->wherePivot('favorite_slot', $slot)->pluck('games.id'),
            ['favorite_slot' => null]
        );

        // 2. Asignar el nuevo juego al hueco
        // Comprobamos si el juego ya estaba en la librería
        $exists = $user->library()->where('game_id', $gameId)->exists();

        if ($exists) {
            $user->library()->updateExistingPivot($gameId, ['favorite_slot' => $slot]);
        } else {
            // Si no lo tenía, lo añadimos (y de paso le damos like automático si quieres)
            $user->library()->attach($gameId, ['favorite_slot' => $slot, 'liked' => true]);
        }

        return back()->with('status', 'favorito-actualizado');
    }
}
