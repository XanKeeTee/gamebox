<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function toggle(User $user)
    {
        $me = Auth::user();

        // No puedes seguirte a ti mismo
        if ($me->id === $user->id) {
            return back();
        }

        if ($me->isFollowing($user)) {
            // Si ya lo sigo, lo borro (Unfollow)
            $me->following()->detach($user->id);
        } else {
            // Si no lo sigo, lo añado (Follow)
            $me->following()->attach($user->id);
        }

        return back();
    }
}