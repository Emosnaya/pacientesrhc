<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PacienteArchivoCompartido extends Model
{
    public $timestamps = false;

    protected $table = 'paciente_archivo_compartidos';

    protected $fillable = [
        'paciente_archivo_id',
        'clinica_id',
        'compartido_at',
    ];

    public function archivo()
    {
        return $this->belongsTo(PacienteArchivo::class, 'paciente_archivo_id');
    }

    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }
}
