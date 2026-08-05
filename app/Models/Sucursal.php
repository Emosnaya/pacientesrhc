<?php

namespace App\Models;

use App\Services\GoogleGeocodingService;
use App\Services\SucursalHorarioService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        'clinica_id',
        'nombre',
        'codigo',
        'direccion',
        'telefono',
        'email',
        'ciudad',
        'estado',
        'codigo_postal',
        'latitud',
        'longitud',
        'geocoded_at',
        'geocode_status',
        'horarios_atencion',
        'es_principal',
        'activa',
        'visible_directorio',
        'notas',
        'tipo_clinica',
        'modulos_habilitados',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'activa' => 'boolean',
        'visible_directorio' => 'boolean',
        'modulos_habilitados' => 'array',
        'horarios_atencion' => 'array',
        'latitud' => 'float',
        'longitud' => 'float',
        'geocoded_at' => 'datetime',
    ];

    protected $appends = ['direccion_completa', 'tiene_coordenadas'];

    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function pacientes()
    {
        return $this->hasMany(Paciente::class);
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function scopePrincipal($query)
    {
        return $query->where('es_principal', true);
    }

    public function scopeDeClinica($query, $clinicaId)
    {
        return $query->where('clinica_id', $clinicaId);
    }

    public function scopeVisiblesDirectorio($query)
    {
        return $query->where('activa', true)->where('visible_directorio', true);
    }

    public function getDireccionCompletaAttribute(): string
    {
        return app(GoogleGeocodingService::class)->buildAddressFromParts(
            $this->direccion,
            $this->ciudad,
            $this->estado,
            $this->codigo_postal
        );
    }

    public function getTieneCoordenadasAttribute(): bool
    {
        return $this->latitud !== null && $this->longitud !== null;
    }

    /**
     * Geocodifica si cambió la dirección. Conserva las últimas coordenadas válidas si falla.
     */
    public function syncGeocode(bool $force = false): void
    {
        $addressChanged = $this->wasRecentlyCreated
            || $this->wasChanged(['direccion', 'ciudad', 'estado', 'codigo_postal'])
            || $force;

        if (! $addressChanged && $this->tiene_coordenadas) {
            return;
        }

        $address = $this->direccion_completa;
        if ($address === '' || $address === 'México') {
            $this->forceFill([
                'geocode_status' => 'EMPTY',
                'geocoded_at' => now(),
            ])->saveQuietly();

            return;
        }

        $result = app(GoogleGeocodingService::class)->geocode($address);

        if ($result['ok']) {
            $this->forceFill([
                'latitud' => $result['lat'],
                'longitud' => $result['lng'],
                'geocode_status' => 'OK',
                'geocoded_at' => now(),
            ])->saveQuietly();

            return;
        }

        // Conserva coords previas y solo actualiza el estado del intento.
        $this->forceFill([
            'geocode_status' => $result['status'],
            'geocoded_at' => now(),
        ])->saveQuietly();
    }

    public function horariosNormalizados(): array
    {
        return app(SucursalHorarioService::class)->normalize($this->horarios_atencion);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sucursal) {
            if (empty($sucursal->codigo)) {
                $sucursal->codigo = static::generarCodigo($sucursal->clinica_id);
            }
            if ($sucursal->horarios_atencion === null) {
                $sucursal->horarios_atencion = app(SucursalHorarioService::class)->defaultHorarios();
            }
            if ($sucursal->visible_directorio === null) {
                $sucursal->visible_directorio = true;
            }
        });

        static::saving(function ($sucursal) {
            if (is_array($sucursal->horarios_atencion)) {
                $sucursal->horarios_atencion = app(SucursalHorarioService::class)->normalize($sucursal->horarios_atencion);
            }
        });

        static::saved(function (Sucursal $sucursal) {
            if ($sucursal->wasRecentlyCreated
                || $sucursal->wasChanged(['direccion', 'ciudad', 'estado', 'codigo_postal'])
            ) {
                $sucursal->syncGeocode();
            }
        });
    }

    private static function generarCodigo($clinicaId)
    {
        $count = static::where('clinica_id', $clinicaId)->count();

        return 'SUC-'.str_pad($clinicaId, 3, '0', STR_PAD_LEFT).'-'.str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }
}
