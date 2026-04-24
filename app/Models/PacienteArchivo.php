<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PacienteArchivo extends Model
{
    use HasFactory;

    protected $table = 'paciente_archivos';

    protected $fillable = [
        'paciente_id',
        'clinica_id',
        'nombre_original',
        'ruta',
        'mime_type',
        'tamanio',
        'descripcion',
        'subido_por_paciente',
        'subido_por_user_id',
        'visible_en_portal',
    ];

    protected $casts = [
        'subido_por_paciente' => 'boolean',
        'visible_en_portal'   => 'boolean',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    public function subidoPorUser()
    {
        return $this->belongsTo(User::class, 'subido_por_user_id');
    }

    public function compartidos()
    {
        return $this->hasMany(PacienteArchivoCompartido::class, 'paciente_archivo_id');
    }

    public function clinicasCompartidas()
    {
        return $this->belongsToMany(Clinica::class, 'paciente_archivo_compartidos', 'paciente_archivo_id', 'clinica_id')
            ->withPivot('compartido_at');
    }

    /** Tamaño legible (ej. "1.2 MB") */
    public function getTamanioLegibleAttribute(): string
    {
        $bytes = $this->tamanio ?? 0;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}
