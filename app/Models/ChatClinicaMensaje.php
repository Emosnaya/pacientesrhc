<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatClinicaMensaje extends Model
{
    protected $table = 'chat_clinica_mensajes';

    protected $fillable = [
        'clinica_id',
        'user_id',
        'mensaje',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
