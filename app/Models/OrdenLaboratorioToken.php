<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenLaboratorioToken extends Model
{
    protected $table = 'orden_laboratorio_tokens';

    protected $fillable = ['orden_id', 'token', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public function orden(): BelongsTo
    {
        return $this->belongsTo(OrdenLaboratorio::class, 'orden_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
