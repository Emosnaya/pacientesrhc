<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatUsoCfdi extends Model
{
    protected $table = 'cat_usos_cfdi';
    protected $primaryKey = 'clave';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'clave',
        'descripcion',
        'persona_fisica',
        'persona_moral',
        'activo',
    ];

    protected $casts = [
        'persona_fisica' => 'boolean',
        'persona_moral' => 'boolean',
        'activo' => 'boolean',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeParaPersonaFisica($query)
    {
        return $query->where('persona_fisica', true);
    }

    public function scopeParaPersonaMoral($query)
    {
        return $query->where('persona_moral', true);
    }

    /**
     * Usos CFDI comunes para servicios médicos
     */
    public function scopeParaServiciosMedicos($query)
    {
        return $query->whereIn('clave', ['D01', 'D02', 'D07', 'G03', 'S01']);
    }
}
