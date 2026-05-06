<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMensaje extends Model
{
    protected $table = 'chat_mensajes';

    protected $fillable = [
        'conversacion_id',
        'user_id',
        'mensaje',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function conversacion()
    {
        return $this->belongsTo(ChatConversacion::class, 'conversacion_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
