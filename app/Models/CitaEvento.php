<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitaEvento extends Model
{
    protected $table = 'cita_eventos';

    protected $fillable = [
        'cita_id',
        'tipo',
        'actor',
        'actor_user_id',
        'meta',
        'mensaje',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function actorUser()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
