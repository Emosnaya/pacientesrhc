<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatConversacion extends Model
{
    protected $table = 'chat_conversaciones';

    protected $fillable = [
        'clinica_id',
        'tipo',
        'nombre',
        'created_by',
    ];

    public function participantes()
    {
        return $this->hasMany(ChatParticipante::class, 'conversacion_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_participantes', 'conversacion_id', 'user_id')
                    ->withPivot('last_read_at')
                    ->withTimestamps();
    }

    public function mensajes()
    {
        return $this->hasMany(ChatMensaje::class, 'conversacion_id')->orderBy('created_at', 'asc');
    }

    public function ultimoMensaje()
    {
        return $this->hasOne(ChatMensaje::class, 'conversacion_id')->latestOfMany();
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
