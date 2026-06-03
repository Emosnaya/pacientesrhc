<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuscripcionFacturas extends Model
{
    public const SCOPE_CLINICA = 'clinica';

    public const SCOPE_USUARIO = 'usuario';

    protected $table = 'suscripcion_facturas';

    protected $fillable = [
        'user_id',
        'billing_scope',
        'clinica_id',
        'plan',
        'estado',
        'cantidad_facturas_limite',
        'cantidad_facturas_usadas',
        'fecha_inicio',
        'fecha_vencimiento',
        'precio_mensual',
        'facturapi_subscription_id',
        'stripe_checkout_session_id',
        'stripe_subscription_id',
        'stripe_payment_intent_id',
        'notas',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_vencimiento' => 'date',
        'precio_mensual' => 'decimal:2',
    ];

    const ESTADO_ACTIVA = 'activa';
    const ESTADO_PAUSADA = 'pausada';
    const ESTADO_CANCELADA = 'cancelada';
    const ESTADO_VENCIDA = 'vencida';

    const PLAN_BASICO = 'basico';
    const PLAN_PRO = 'pro';
    const PLAN_ENTERPRISE = 'enterprise';

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function esPorUsuario(): bool
    {
        return $this->billing_scope === self::SCOPE_USUARIO || $this->user_id !== null;
    }

    public function getNombrePlan(): string
    {
        return match ($this->plan) {
            self::PLAN_BASICO => 'Plan Básico',
            self::PLAN_PRO => 'Plan Pro',
            self::PLAN_ENTERPRISE => 'Plan Enterprise',
            default => 'Desconocido',
        };
    }

    public function getDescripcionPlan(): string
    {
        return match ($this->plan) {
            self::PLAN_BASICO => '1-100 facturas/mes',
            self::PLAN_PRO => '101-300 facturas/mes',
            self::PLAN_ENTERPRISE => '301+ facturas/mes',
            default => 'Sin descripción',
        };
    }

    public function puedeCrearFactura(): bool
    {
        return $this->estado === self::ESTADO_ACTIVA
            && $this->cantidad_facturas_usadas < $this->cantidad_facturas_limite
            && now()->isBetween($this->fecha_inicio, $this->fecha_vencimiento);
    }

    public function incrementarFacturasUsadas(): void
    {
        if ($this->cantidad_facturas_usadas < $this->cantidad_facturas_limite) {
            $this->increment('cantidad_facturas_usadas');
        }
    }

    public function estaProximaAVencer(): bool
    {
        return now()->diffInDays($this->fecha_vencimiento) <= 7 && $this->estado === self::ESTADO_ACTIVA;
    }

    public function estaVencida(): bool
    {
        return now()->isAfter($this->fecha_vencimiento) && $this->estado === self::ESTADO_ACTIVA;
    }

    public function actualizarEstadoVencimiento(): void
    {
        if ($this->estaVencida()) {
            $this->update(['estado' => self::ESTADO_VENCIDA]);
        }
    }

    public static function facturacionEsPorUsuario(?Clinica $clinica): bool
    {
        return (bool) $clinica?->es_consultorio_privado;
    }

    public static function obtenerActivaPorClinica(int $clinicaId): ?self
    {
        return self::query()
            ->where('clinica_id', $clinicaId)
            ->where('billing_scope', self::SCOPE_CLINICA)
            ->where('estado', self::ESTADO_ACTIVA)
            ->first();
    }

    public static function obtenerActivaPorUsuario(int $userId): ?self
    {
        return self::query()
            ->where('user_id', $userId)
            ->where('billing_scope', self::SCOPE_USUARIO)
            ->where('estado', self::ESTADO_ACTIVA)
            ->first();
    }

    /**
     * Suscripción vigente según el tipo de espacio (consultorio = doctor, clínica = workspace).
     */
    public static function obtenerActivaParaContexto(User $user, ?int $clinicaId = null): ?self
    {
        $clinicaId = $clinicaId ?? $user->clinica_efectiva_id;
        $clinica = Clinica::find($clinicaId);

        if (self::facturacionEsPorUsuario($clinica)) {
            return self::obtenerActivaPorUsuario($user->id);
        }

        return $clinicaId ? self::obtenerActivaPorClinica($clinicaId) : null;
    }

    public static function usuarioTieneModuloActivo(User $user, ?Clinica $clinica = null): bool
    {
        $clinica ??= Clinica::find($user->clinica_efectiva_id);

        if (self::facturacionEsPorUsuario($clinica)) {
            $suscripcion = self::obtenerActivaPorUsuario($user->id);
            if ($suscripcion) {
                $suscripcion->actualizarEstadoVencimiento();

                return $suscripcion->estado === self::ESTADO_ACTIVA;
            }

            return false;
        }

        return (bool) $clinica?->facturacion_addon_activo;
    }

    public function facturasRestantes(): int
    {
        return max(0, $this->cantidad_facturas_limite - $this->cantidad_facturas_usadas);
    }

    /**
     * @return array{ok: bool, suscripcion: ?self, message?: string, http_status?: int, error_key?: string}
     */
    public static function validarEmision(int $clinicaId, ?User $user = null): array
    {
        $user ??= auth()->user();
        $clinica = Clinica::find($clinicaId);

        $suscripcion = ($user && self::facturacionEsPorUsuario($clinica))
            ? self::obtenerActivaPorUsuario($user->id)
            : self::obtenerActivaPorClinica($clinicaId);

        if (! $suscripcion) {
            $message = self::facturacionEsPorUsuario($clinica)
                ? 'Contrata tu plan de facturación personal en Perfil → Facturación CFDI.'
                : 'Debes contratar un plan de facturación para emitir facturas electrónicas.';

            return [
                'ok' => false,
                'suscripcion' => null,
                'message' => $message,
                'http_status' => 403,
                'error_key' => 'requiere_plan',
            ];
        }

        $suscripcion->actualizarEstadoVencimiento();
        $suscripcion->refresh();

        if ($suscripcion->estado !== self::ESTADO_ACTIVA) {
            return [
                'ok' => false,
                'suscripcion' => $suscripcion,
                'message' => 'Tu plan de facturación no está activo. Renueva o contrata un plan.',
                'http_status' => 403,
                'error_key' => 'plan_inactivo',
            ];
        }

        if (! $suscripcion->puedeCrearFactura()) {
            return [
                'ok' => false,
                'suscripcion' => $suscripcion,
                'message' => 'Has alcanzado el límite de facturas de tu plan. Actualiza tu plan para continuar.',
                'http_status' => 402,
                'error_key' => 'limite_alcanzado',
            ];
        }

        return ['ok' => true, 'suscripcion' => $suscripcion];
    }

    public static function estadoFacturacionParaClinica(int $clinicaId, ?Clinica $clinica = null, ?User $user = null): array
    {
        $user ??= auth()->user();
        $clinica ??= Clinica::find($clinicaId);
        $esConsultorio = self::facturacionEsPorUsuario($clinica);

        $suscripcion = ($user && $esConsultorio)
            ? self::obtenerActivaPorUsuario($user->id)
            : self::obtenerActivaPorClinica($clinicaId);

        if ($suscripcion) {
            $suscripcion->actualizarEstadoVencimiento();
            $suscripcion->refresh();
        }

        $tienePlan = $suscripcion && $suscripcion->estado === self::ESTADO_ACTIVA;

        $efirmaDoctor = null;
        if ($esConsultorio && $user) {
            $efirmaDoctor = Efirma::paraFacturacionUsuario($user->id);
            $tieneCsd = (bool) (
                $efirmaDoctor?->listaParaFacturapi()
                && (
                    $efirmaDoctor->tipo === 'fiscal'
                    || ($efirmaDoctor->usar_para_facturacion ?? false)
                )
            );
        } else {
            $tieneCsd = (bool) $clinica?->facturapi_organization_id;
        }

        $puedeEmitir = $tienePlan && $suscripcion->puedeCrearFactura();
        $faltaCsd = $tienePlan && ! $tieneCsd;
        $faltaToggleFacturacion = $esConsultorio && $tienePlan
            && $efirmaDoctor?->tipo === 'personal'
            && $efirmaDoctor?->listaParaFacturapi()
            && ! ($efirmaDoctor->usar_para_facturacion ?? false);

        return [
            'tiene_plan' => $tienePlan,
            'plan_nombre' => $tienePlan ? $suscripcion->getNombrePlan() : null,
            'limite' => $tienePlan ? $suscripcion->cantidad_facturas_limite : 0,
            'usadas' => $tienePlan ? $suscripcion->cantidad_facturas_usadas : 0,
            'restantes' => $tienePlan ? $suscripcion->facturasRestantes() : 0,
            'fecha_inicio_plan' => $tienePlan ? $suscripcion->fecha_inicio->format('Y-m-d') : null,
            'fecha_vencimiento_plan' => $tienePlan ? $suscripcion->fecha_vencimiento->format('Y-m-d') : null,
            'tiene_csd' => $tieneCsd,
            'es_consultorio_privado' => $esConsultorio,
            'facturacion_por_usuario' => $esConsultorio,
            'puede_facturar' => $puedeEmitir && $tieneCsd,
            'requiere_plan' => ! $tienePlan,
            'falta_csd' => $faltaCsd,
            'falta_efirma_personal' => $esConsultorio && $tienePlan && ! $efirmaDoctor?->listaParaFacturapi(),
            'falta_toggle_facturacion' => $faltaToggleFacturacion,
            'limite_alcanzado' => $tienePlan && ! $suscripcion->puedeCrearFactura(),
        ];
    }

    public function sincronizarAddonClinica(bool $activo): void
    {
        if ($this->esPorUsuario() || ! $this->clinica_id) {
            return;
        }

        Clinica::where('id', $this->clinica_id)->update(['facturacion_addon_activo' => $activo]);
    }
}
