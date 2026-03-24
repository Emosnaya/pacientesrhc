<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatRegimenFiscal extends Model
{
    protected $table = 'cat_regimenes_fiscales';
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
}
