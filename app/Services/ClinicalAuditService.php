<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Registro de accesos y eventos clínicos (bitácora) sin duplicar datos sensibles en logs de aplicación.
 */
class ClinicalAuditService
{
    public static function logAccess(User $user, string $modelClass, int $resourceId, string $descripcion, string $evento = 'viewed'): void
    {
        try {
            AuditLog::create([
                'user_id' => $user->id,
                'clinica_id' => $user->clinica_efectiva_id ?? $user->clinica_id,
                'sucursal_id' => $user->sucursal_id,
                'evento' => $evento,
                'modelo_afectado' => $modelClass,
                'id_recurso' => $resourceId,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'datos_anteriores' => null,
                'datos_nuevos' => null,
                'descripcion' => $descripcion,
            ]);
        } catch (\Throwable $e) {
            Log::warning('ClinicalAuditService: '.$e->getMessage());
        }
    }
}
