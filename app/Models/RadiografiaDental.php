<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadiografiaDental extends Model
{
    use HasFactory;

    protected $table = 'radiografias_dentales';

    protected $fillable = [
        'paciente_id',
        'user_id',
        'clinica_id',
        'titulo',
        'descripcion',
        'ruta_archivo',
        'tipo_radiografia',
        'fecha'
    ];

    protected $casts = [
        'fecha' => 'date'
    ];

    // Relaciones
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }
}
