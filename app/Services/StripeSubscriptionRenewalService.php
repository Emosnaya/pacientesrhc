<?php

namespace App\Services;

use App\Models\Clinica;
use App\Models\Payment;
use App\Models\SuscripcionFacturas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;

/**
 * Sincroniza renovaciones automticas de Stripe con fechas de acceso en LynkaMed.
 */
class StripeSubscriptionRenewalService
{
    public function processInvoicePaymentSucceeded(object $invoice): void
    {
        if (($invoice->status ?? '') !== 'paid') {
            return;
        }

        $subscriptionId = $this->resolveSubscriptionId($invoice);
        if (! $subscriptionId) {
            return;
        }

        if (Payment::where('stripe_invoice_id', $invoice->id)->exists()) {
            Log::info('Stripe invoice ya procesada', ['invoice_id' => $invoice->id]);

            return;
        }

        $subscription = $this->retrieveSubscription($subscriptionId);
        if (! $subscription) {
            return;
        }

        $periodEnd = self::periodEndFromStripe($subscription);
        if (! $periodEnd) {
            Log::error('Stripe invoice sin periodo de suscripcin', [
                'subscription_id' => $subscriptionId,
                'invoice_id' => $invoice->id,
            ]);

            return;
        }
        $billingReason = $invoice->billing_reason ?? null;
        $isRenewal = $billingReason === 'subscription_cycle';

        if ($this->renewClinicaFromStripe($subscription, $invoice, $periodEnd, $isRenewal)) {
            return;
        }

        if ($this->renewFacturacionFromStripe($subscription, $invoice, $periodEnd, $isRenewal)) {
            return;
        }

        Log::info('Stripe invoice sin suscripcin LynkaMed vinculada', [
            'subscription_id' => $subscriptionId,
            'invoice_id' => $invoice->id,
        ]);
    }

    /**
     * Programa cancelacin al final del periodo pagado (no corta acceso de inmediato).
     */
    public function scheduleCancelAtPeriodEnd(string $stripeSubscriptionId): ?Carbon
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $subscription = StripeSubscription::update($stripeSubscriptionId, [
            'cancel_at_period_end' => true,
        ]);

        return self::periodEndFromStripe($subscription);
    }

    /**
     * Corrige fecha_vencimiento corrupta (p. ej. 1969) cuando hay suscripcin activa en Stripe.
     */
    public function repairClinicaStripePeriodIfNeeded(Clinica $clinica): bool
    {
        if (! $clinica->stripe_subscription_id) {
            return false;
        }

        $fecha = $clinica->fecha_vencimiento;
        $fechaInvalida = ! $fecha || $fecha->year < 2000;
        $vigente = $fecha && SubscriptionStatusService::fechaVencimientoVigente($fecha);

        if (! $fechaInvalida && $vigente) {
            return false;
        }

        $subscription = $this->retrieveSubscription($clinica->stripe_subscription_id);
        if (! $subscription || ! self::periodEndFromStripe($subscription)) {
            return false;
        }

        $this->syncClinicaPeriodFromStripe($clinica, $subscription);

        Log::info('Fecha de vencimiento reparada desde Stripe', [
            'clinica_id' => $clinica->id,
            'fecha_vencimiento' => $clinica->fresh()->fecha_vencimiento?->format('Y-m-d'),
        ]);

        return true;
    }

    public function syncClinicaPeriodFromStripe(Clinica $clinica, object $subscription): void
    {
        $periodEnd = self::periodEndFromStripe($subscription);
        if (! $periodEnd) {
            Log::error('syncClinicaPeriodFromStripe: sin current_period_end', [
                'subscription_id' => $subscription->id ?? null,
            ]);

            return;
        }

        $clinica->update([
            'activa' => true,
            'pagado' => true,
            'fecha_vencimiento' => $periodEnd,
            'next_billing_date' => $periodEnd,
            'stripe_subscription_id' => $subscription->id,
            'billing_cycle' => $this->intervalToBillingCycle($subscription),
        ]);

        if ($clinica->propietario_user_id) {
            User::where('id', $clinica->propietario_user_id)->update([
                'tiene_suscripcion_consultorio' => true,
                'suscripcion_fin' => $periodEnd,
                'stripe_subscription_id' => $subscription->id,
                'ciclo_facturacion' => $this->intervalToBillingCycle($subscription),
            ]);
        }
    }

    public function syncFacturacionPeriodFromStripe(
        SuscripcionFacturas $suscripcion,
        object $subscription,
        bool $resetMonthlyCounter
    ): void {
        $periodStart = self::periodStartFromStripe($subscription) ?? now()->startOfDay();
        $periodEnd = self::periodEndFromStripe($subscription);
        if (! $periodEnd) {
            Log::error('syncFacturacionPeriodFromStripe: sin current_period_end', [
                'subscription_id' => $subscription->id ?? null,
            ]);

            return;
        }

        $updates = [
            'estado' => SuscripcionFacturas::ESTADO_ACTIVA,
            'fecha_inicio' => $periodStart,
            'fecha_vencimiento' => $periodEnd,
            'stripe_subscription_id' => $subscription->id,
        ];

        if ($resetMonthlyCounter) {
            $updates['cantidad_facturas_usadas'] = 0;
        }

        $suscripcion->update($updates);
        $suscripcion->sincronizarAddonClinica(true);
    }

    /**
     * Finaliza acceso solo cuando Stripe elimin la suscripcin (periodo ya termin).
     */
    public function finalizeClinicaSubscriptionEnded(object $stripeSubscription): void
    {
        $clinica = Clinica::where('stripe_subscription_id', $stripeSubscription->id)->first();
        if (! $clinica) {
            return;
        }

        $periodEnd = self::periodEndFromStripe($stripeSubscription);

        if ($periodEnd && now()->lessThan($periodEnd)) {
            $clinica->update(['fecha_vencimiento' => $periodEnd]);

            return;
        }

        $clinica->update([
            'pagado' => false,
            'stripe_subscription_id' => null,
        ]);

        if ($clinica->propietario_user_id) {
            User::where('id', $clinica->propietario_user_id)->update([
                'stripe_subscription_id' => null,
            ]);
        }

        Log::info('Suscripcin LynkaMed finalizada', ['clinica_id' => $clinica->id]);
    }

    public function finalizeFacturacionSubscriptionEnded(object $stripeSubscription): void
    {
        $suscripcion = SuscripcionFacturas::where('stripe_subscription_id', $stripeSubscription->id)
            ->whereIn('estado', [SuscripcionFacturas::ESTADO_ACTIVA, SuscripcionFacturas::ESTADO_VENCIDA])
            ->orderByDesc('id')
            ->first();

        if (! $suscripcion) {
            return;
        }

        $periodEnd = $suscripcion->fecha_vencimiento
            ? Carbon::parse($suscripcion->fecha_vencimiento)->endOfDay()
            : (self::periodEndFromStripe($stripeSubscription) ?? now());

        if (now()->lessThan($periodEnd)) {
            return;
        }

        $suscripcion->update(['estado' => SuscripcionFacturas::ESTADO_CANCELADA]);
        $suscripcion->sincronizarAddonClinica(false);

        Log::info('Plan de facturacin finalizado', ['clinica_id' => $suscripcion->clinica_id]);
    }

    private function renewClinicaFromStripe(
        object $subscription,
        object $invoice,
        Carbon $periodEnd,
        bool $isRenewal
    ): bool {
        $clinica = Clinica::where('stripe_subscription_id', $subscription->id)->first();
        if (! $clinica) {
            return false;
        }

        DB::transaction(function () use ($clinica, $subscription, $invoice, $periodEnd, $isRenewal) {
            $this->syncClinicaPeriodFromStripe($clinica, $subscription);
            $this->recordInvoicePayment($clinica, $invoice, $isRenewal ? 'lynkamed_renewal' : 'lynkamed_subscription');
        });

        Log::info('Renovacin automtica LynkaMed aplicada', [
            'clinica_id' => $clinica->id,
            'fecha_vencimiento' => $periodEnd->format('Y-m-d'),
            'invoice_id' => $invoice->id,
        ]);

        return true;
    }

    private function renewFacturacionFromStripe(
        object $subscription,
        object $invoice,
        Carbon $periodEnd,
        bool $isRenewal
    ): bool {
        $suscripcion = SuscripcionFacturas::where('stripe_subscription_id', $subscription->id)
            ->orderByDesc('id')
            ->first();

        if (! $suscripcion) {
            return false;
        }

        DB::transaction(function () use ($suscripcion, $subscription, $invoice, $isRenewal) {
            $this->syncFacturacionPeriodFromStripe($suscripcion, $subscription, $isRenewal);
            $clinica = Clinica::find($suscripcion->clinica_id);
            if ($clinica) {
                $this->recordInvoicePayment($clinica, $invoice, $isRenewal ? 'facturacion_renewal' : 'facturacion_subscription');
            }
        });

        Log::info('Renovacin automtica plan facturacin aplicada', [
            'clinica_id' => $suscripcion->clinica_id,
            'fecha_vencimiento' => $periodEnd->format('Y-m-d'),
            'invoice_id' => $invoice->id,
        ]);

        return true;
    }

    private function recordInvoicePayment(Clinica $clinica, object $invoice, string $type): void
    {
        $amount = ($invoice->amount_paid ?? $invoice->total ?? 0) / 100;

        Payment::create([
            'clinica_id' => $clinica->id,
            'user_id' => $clinica->propietario_user_id,
            'amount' => $amount,
            'currency' => strtoupper($invoice->currency ?? 'mxn'),
            'status' => 'completed',
            'payment_method' => 'stripe',
            'stripe_payment_id' => $invoice->payment_intent ?? $invoice->id,
            'stripe_invoice_id' => $invoice->id,
            'metadata' => [
                'type' => $type,
                'billing_reason' => $invoice->billing_reason ?? null,
                'subscription_id' => $this->resolveSubscriptionId($invoice),
            ],
        ]);
    }

    private function resolveSubscriptionId(object $invoice): ?string
    {
        $sub = $invoice->subscription ?? null;

        return is_string($sub) ? $sub : ($sub->id ?? null);
    }

    private function retrieveSubscription(string $subscriptionId): ?object
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            return StripeSubscription::retrieve($subscriptionId);
        } catch (\Exception $e) {
            Log::error('No se pudo obtener suscripcin Stripe: '.$e->getMessage(), [
                'subscription_id' => $subscriptionId,
            ]);

            return null;
        }
    }

    private function intervalToBillingCycle(object $subscription): string
    {
        $interval = $subscription->items->data[0]->price->recurring->interval ?? 'month';

        return $interval === 'year' ? 'anual' : 'mensual';
    }

    /**
     * Stripe API reciente expone el periodo en subscription items, no siempre en la raz.
     */
    public static function periodEndTimestamp(object $subscription): ?int
    {
        if (! empty($subscription->current_period_end)) {
            return (int) $subscription->current_period_end;
        }

        foreach ($subscription->items->data ?? [] as $item) {
            if (! empty($item->current_period_end)) {
                return (int) $item->current_period_end;
            }
        }

        return null;
    }

    public static function periodStartTimestamp(object $subscription): ?int
    {
        if (! empty($subscription->current_period_start)) {
            return (int) $subscription->current_period_start;
        }

        foreach ($subscription->items->data ?? [] as $item) {
            if (! empty($item->current_period_start)) {
                return (int) $item->current_period_start;
            }
        }

        return null;
    }

    public static function periodEndFromStripe(object $subscription): ?Carbon
    {
        $ts = self::periodEndTimestamp($subscription);
        if (! $ts || $ts < strtotime('2000-01-01')) {
            return null;
        }

        return Carbon::createFromTimestamp($ts)->endOfDay();
    }

    public static function periodStartFromStripe(object $subscription): ?Carbon
    {
        $ts = self::periodStartTimestamp($subscription);
        if (! $ts || $ts < strtotime('2000-01-01')) {
            return null;
        }

        return Carbon::createFromTimestamp($ts)->startOfDay();
    }
}
