<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoporteMensaje extends Model
{
    protected $table = 'soporte_mensajes';

    protected $fillable = [
        'ticket_id',
        'sender_id',
        'sender_type',
        'mensaje',
        'leido_at',
    ];

    protected $casts = [
        'leido_at'   => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(SoporteTicket::class, 'ticket_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
