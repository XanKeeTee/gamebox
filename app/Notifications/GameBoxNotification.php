<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GameBoxNotification extends Notification
{
    use Queueable;

    public $type;       // 'like', 'comment', 'follow'
    public $message;    // "A Juan le gustó tu reseña"
    public $url;        // A dónde te lleva el clic
    public $fromUser;   // Quién lo hizo (para mostrar su avatar)

    public function __construct($type, $message, $url, $fromUser)
    {
        $this->type = $type;
        $this->message = $message;
        $this->url = $url;
        $this->fromUser = $fromUser;
    }

    public function via($notifiable)
    {
        return ['database']; // Guardar en la tabla 'notifications'
    }

    // Qué datos guardamos en la base de datos
    public function toArray($notifiable)
    {
        return [
            'type' => $this->type,
            'message' => $this->message,
            'url' => $this->url,
            'user_id' => $this->fromUser->id,
            'user_name' => $this->fromUser->name,
            'user_avatar' => $this->fromUser->avatar,
        ];
    }
}