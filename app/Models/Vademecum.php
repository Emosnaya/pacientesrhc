<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vademecum extends Model
{
    protected $table = 'vademecum';

    protected $fillable = [
        'nombre_generico',
        'nombre_comercial',
        'presentacion',
        'concentracion',
        'via_administracion',
        'categoria',
        'indicaciones',
        'contraindicaciones',
        'dosis_sugerida',
        'duracion_sugerida',
        'requiere_receta',
        'controlado',
        'activo',
    ];

    protected $casts = [
        'requiere_receta' => 'boolean',
        'controlado' => 'boolean',
        'activo' => 'boolean',
    ];

    // ─────────────────────────────── Scopes ───────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('nombre_generico', 'LIKE', "%{$term}%")
              ->orWhere('nombre_comercial', 'LIKE', "%{$term}%");
        })->activos();
    }

    public function scopeCategoria($query, $categoria)
    {
        if ($categoria) {
            return $query->where('categoria', $categoria);
        }
        return $query;
    }

    public function scopeCategorias($query, array $categorias)
    {
        return $query->whereIn('categoria', $categorias);
    }

    // ─────────────────────────────── Accessors ───────────────────────────────

    public function getNombreCompletoAttribute(): string
    {
        $nombre = $this->nombre_generico;
        if ($this->nombre_comercial) {
            $nombre .= " ({$this->nombre_comercial})";
        }
        if ($this->concentracion) {
            $nombre .= " - {$this->concentracion}";
        }
        return $nombre;
    }

    public function getPresentacionCompletaAttribute(): string
    {
        $parts = array_filter([
            $this->presentacion,
            $this->concentracion,
        ]);
        return implode(' · ', $parts) ?: '—';
    }
}
