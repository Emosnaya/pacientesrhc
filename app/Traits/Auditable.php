<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     */
    public static function bootAuditable()
    {
        // Evento: Creación
        static::created(function ($model) {
            $model->auditLog('created', null, $model->getAuditableAttributes());
        });

        // Evento: Actualización
        static::updated(function ($model) {
            $model->auditLog(
                'updated',
                $model->getOriginal(),
                $model->getAuditableAttributes()
            );
        });

        // Evento: Eliminación
        static::deleted(function ($model) {
            $model->auditLog('deleted', $model->getAuditableAttributes(), null);
        });
    }

    /**
     * Registra un evento de auditoría
     */
    protected function auditLog(string $evento, ?array $datosAnteriores, ?array $datosNuevos)
    {
        // Solo auditar si hay un usuario autenticado (excepto para seeders/tests)
        if (!Auth::check() && !app()->runningInConsole()) {
            return;
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'clinica_id' => $this->clinica_id ?? Auth::user()?->clinica_id ?? null,
            'sucursal_id' => $this->sucursal_id ?? session('sucursal_id') ?? null,
            'evento' => $evento,
            'modelo_afectado' => get_class($this),
            'id_recurso' => $this->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'datos_anteriores' => $this->filterSensitiveData($datosAnteriores),
            'datos_nuevos' => $this->filterSensitiveData($datosNuevos),
            'descripcion' => $this->getAuditDescription($evento),
        ]);
    }

    /**
     * Obtiene los atributos auditables del modelo
     */
    protected function getAuditableAttributes(): array
    {
        // Si el modelo define atributos específicos para auditar
        if (property_exists($this, 'auditableAttributes')) {
            return collect($this->auditableAttributes)
                ->mapWithKeys(fn($attr) => [$attr => $this->$attr])
                ->toArray();
        }

        // Por defecto, auditar todos los fillable
        return $this->only($this->getFillable());
    }

    /**
     * Filtra datos sensibles para no almacenar contraseñas en los logs
     */
    protected function filterSensitiveData(?array $data): ?array
    {
        if (!$data) {
            return null;
        }

        $sensitiveFields = ['password', 'password_confirmation', 'remember_token', 'api_token'];
        
        return collect($data)
            ->except($sensitiveFields)
            ->toArray();
    }

    /**
     * Genera una descripción legible del evento
     */
    protected function getAuditDescription(string $evento): string
    {
        $modelName = class_basename($this);
        $userName = Auth::user()?->nombre ?? 'Sistema';

        $descriptions = [
            'created' => "{$userName} creó {$modelName} #{$this->id}",
            'updated' => "{$userName} actualizó {$modelName} #{$this->id}",
            'deleted' => "{$userName} eliminó {$modelName} #{$this->id}",
            'viewed' => "{$userName} visualizó {$modelName} #{$this->id}",
        ];

        return $descriptions[$evento] ?? "{$userName} ejecutó {$evento} en {$modelName} #{$this->id}";
    }

    /**
     * Registra un evento de visualización (debe llamarse manualmente)
     */
    public function logViewed()
    {
        $this->auditLog('viewed', null, $this->getAuditableAttributes());
    }
}
