<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        // Marcamos todas como leídas al entrar
        Auth::user()->unreadNotifications->markAsRead();

        return view('notifications.index', [
            'notifications' => Auth::user()->notifications()->paginate(20)
        ]);
    }
}
