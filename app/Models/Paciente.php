<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'registro',
        'nombre',
        'apellidoPat',
        'apellidoMat',
        'telefono',
        'email',
        'fechaNacimiento',
        'edad',
        'genero',
        'estadoCivil',
        'profesion',
        'domicilio',
        'talla',
        'peso',
        'cintura',
        'imc',
        'diagnostico',
        'medicamentos',
        'envio',
        'user_id'
    ];

}
