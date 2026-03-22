<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\Clinica;
use App\Models\User;
use App\Models\Sucursal;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;

class SubscriptionController extends Controller
{
    /**
     * Obtener el plan único disponible
     */
    public function getPlans(): JsonResponse
    {
        // Solo retornamos el plan profesional (único)
        $plan = SubscriptionPlan::where('is_active', true)
            ->where('slug', 'profesional')
            ->first();

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan no disponible'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'plan' => $plan
        ]);
    }

    /**
     * Crear sesión de checkout de Stripe
     */
    public function createCheckoutSession(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:mensual,anual',
            'user_data' => 'required|array',
            'user_data.nombre' => 'required|string|max:255',
            'user_data.apellidoPat' => 'required|string|max:255',
            'user_data.email' => 'required|email|unique:users,email',
            'user_data.cedula' => 'required|string|max:50',
            'user_data.password' => 'required|string|min:6',
            'consultorio_data' => 'required|array',
            'consultorio_data.nombre' => 'required|string|max:255',
            'consultorio_data.tipo_clinica' => 'required|string',
        ]);

        try {
            $plan = SubscriptionPlan::findOrFail($request->plan_id);
            $price = $request->billing_cycle === 'anual' 
                ? $plan->price_yearly 
                : $plan->price_monthly;

            Stripe::setApiKey(config('services.stripe.secret'));

            // Crear sesión de Stripe Checkout
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'mxn',
                        'product_data' => [
                            'name' => "Plan {$plan->name} - " . ucfirst($request->billing_cycle),
                            'description' => $plan->description,
                        ],
                        'unit_amount' => round($price * 100), // Stripe usa centavos
                        'recurring' => [
                            'interval' => $request->billing_cycle === 'anual' ? 'year' : 'month',
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => config('app.frontend_url', env('FRONTEND_URL')) . '/registro/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.frontend_url', env('FRONTEND_URL')) . '/registro',
                'metadata' => [
                    'plan_id' => $plan->id,
                    'billing_cycle' => $request->billing_cycle,
                    'user_data' => json_encode($request->user_data),
                    'consultorio_data' => json_encode($request->consultorio_data),
                ],
            ]);

            return response()->json([
                'success' => true,
                'sessionId' => $session->id,
                'url' => $session->url
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating checkout session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la sesión de pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook de Stripe para procesar eventos
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);

            // Procesar evento de checkout completado
            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;
                
                // Verificar si es consultorio adicional o suscripción principal
                $metadata = $session->metadata;
                if (isset($metadata->type) && $metadata->type === 'consultorio_adicional') {
                    $this->fulfillConsultorioAdicional($session);
                } else {
                    $this->fulfillOrder($session);
                }
            }

            // Procesar cancelación de suscripción
            if ($event->type === 'customer.subscription.deleted') {
                $subscription = $event->data->object;
                $this->handleSubscriptionCancellation($subscription);
            }

            return response()->json(['status' => 'success']);

        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid webhook payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid webhook signature: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Activar CONSULTORIO PRIVADO después de pago exitoso
     * NOTA: Este método es SOLO para consultorios privados auto-registrados.
     * Las clínicas organizacionales se registran manualmente.
     */
    private function fulfillOrder($session): void
    {
        try {
            $metadata = $session->metadata;
            $userData = json_decode($metadata->user_data, true);
            $consultorioData = json_decode($metadata->consultorio_data, true);
            $plan = SubscriptionPlan::find($metadata->plan_id);

            DB::beginTransaction();

            // 1. Crear usuario
            $user = User::create([
                'nombre' => $userData['nombre'],
                'apellidoPat' => $userData['apellidoPat'],
                'apellidoMat' => $userData['apellidoMat'] ?? null,
                'email' => $userData['email'],
                'cedula' => $userData['cedula'],
                'password' => Hash::make($userData['password']),
                'rol' => $userData['rol'] ?? null,
                'isAdmin' => true,
                'email_verified' => true,
                'email_verified_at' => now(),
                'imagen' => 'perfiles/avatar-default.png',
            ]);

            // 2. Crear consultorio
            $nextBilling = $metadata->billing_cycle === 'anual' 
                ? now()->addYear() 
                : now()->addMonth();

            $consultorio = Clinica::create([
                'nombre' => $consultorioData['nombre'],
                'tipo_clinica' => $consultorioData['tipo_clinica'],
                'email' => $consultorioData['email'] ?? $user->email,
                'telefono' => $consultorioData['telefono'] ?? null,
                'direccion' => $consultorioData['direccion'] ?? null,
                'plan' => $plan->slug,
                'plan_type' => $plan->slug,
                'billing_cycle' => $metadata->billing_cycle,
                'duration' => $metadata->billing_cycle,
                'pagado' => true,
                'activa' => true,
                'es_consultorio_privado' => true,
                'propietario_user_id' => $user->id,
                'permite_multiples_sucursales' => $plan->max_sucursales > 1,
                'max_pacientes' => $plan->max_pacientes,
                'max_usuarios' => $plan->max_usuarios,
                'max_sucursales' => $plan->max_sucursales,
                'stripe_customer_id' => $session->customer,
                'stripe_subscription_id' => $session->subscription,
                'fecha_vencimiento' => $nextBilling,
                'next_billing_date' => $nextBilling,
                'modulos_habilitados' => [],
            ]);

            // 3. Crear sucursal principal
            $sucursal = Sucursal::create([
                'clinica_id' => $consultorio->id,
                'nombre' => $consultorioData['nombre'],
                'es_principal' => true,
                'activa' => true,
                'direccion' => $consultorioData['direccion'] ?? null,
                'telefono' => $consultorioData['telefono'] ?? null,
            ]);

            // 4. Vincular propietario en user_clinicas
            $user->clinicas()->attach($consultorio->id, [
                'rol_en_clinica' => 'propietario',
                'activa' => true,
            ]);

            // 5. Activar workspace
            $user->update([
                'clinica_activa_id' => $consultorio->id,
                'sucursal_id' => $sucursal->id,
            ]);

            // 6. Registrar pago
            Payment::create([
                'clinica_id' => $consultorio->id,
                'user_id' => $user->id,
                'amount' => $session->amount_total / 100,
                'currency' => strtoupper($session->currency),
                'status' => 'completed',
                'payment_method' => 'stripe',
                'stripe_payment_id' => $session->payment_intent,
                'stripe_invoice_id' => $session->invoice ?? null,
                'metadata' => [
                    'plan' => $plan->name,
                    'billing_cycle' => $metadata->billing_cycle,
                    'session_id' => $session->id,
                ],
            ]);

            DB::commit();

            // 7. Enviar email de bienvenida
            try {
                Mail::to($user->email)->send(new \App\Mail\WelcomeMail($user, $consultorio, $plan));
            } catch (\Exception $e) {
                Log::error('Error sending welcome email: ' . $e->getMessage());
            }

            Log::info("Consultorio activado exitosamente para usuario: {$user->email}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error fulfilling order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Comprar consultorio adicional (addon)
     */
    public function comprarConsultorioAdicional(Request $request): JsonResponse
    {
        $request->validate([
            'billing_cycle' => 'required|in:mensual,anual',
            'consultorio_data' => 'required|array',
            'consultorio_data.nombre' => 'required|string|max:255',
            'consultorio_data.tipo_clinica' => 'required|string',
        ]);

        try {
            $user = $request->user();
            
            // Precio fijo para consultorio adicional
            $precioMensual = 199.00;
            $precioAnual = 1990.00;
            $precio = $request->billing_cycle === 'anual' ? $precioAnual : $precioMensual;

            Stripe::setApiKey(config('services.stripe.secret'));

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'mxn',
                        'product_data' => [
                            'name' => "Consultorio Adicional - " . ucfirst($request->billing_cycle),
                            'description' => 'Agrega otro consultorio a tu suscripción',
                        ],
                        'unit_amount' => round($precio * 100),
                        'recurring' => [
                            'interval' => $request->billing_cycle === 'anual' ? 'year' : 'month',
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => config('app.frontend_url') . '/consultorios/addon-success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.frontend_url') . '/consultorios',
                'customer' => $user->clinica->stripe_customer_id,
                'metadata' => [
                    'type' => 'consultorio_adicional',
                    'user_id' => $user->id,
                    'billing_cycle' => $request->billing_cycle,
                    'consultorio_data' => json_encode($request->consultorio_data),
                ],
            ]);

            return response()->json([
                'success' => true,
                'sessionId' => $session->id,
                'url' => $session->url
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating addon session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la sesión de pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activar consultorio adicional después del pago
     */
    private function fulfillConsultorioAdicional($session): void
    {
        try {
            $metadata = $session->metadata;
            $userId = $metadata->user_id;
            $consultorioData = json_decode($metadata->consultorio_data, true);
            
            $user = User::findOrFail($userId);

            DB::beginTransaction();

            // Crear el nuevo consultorio
            $nuevoConsultorio = Clinica::create([
                'nombre' => $consultorioData['nombre'],
                'tipo_clinica' => $consultorioData['tipo_clinica'],
                'email' => $user->email,
                'telefono' => $consultorioData['telefono'] ?? null,
                'direccion' => $consultorioData['direccion'] ?? null,
                'plan' => 'addon',
                'plan_type' => 'consultorio-adicional',
                'billing_cycle' => $metadata->billing_cycle,
                'pagado' => true,
                'activa' => true,
                'es_consultorio_privado' => true,
                'propietario_user_id' => $user->id,
                'max_pacientes' => 999999,
                'max_usuarios' => 5,
                'max_sucursales' => 1,
                'stripe_subscription_id' => $session->subscription,
                'fecha_vencimiento' => now()->addMonth(),
                'modulos_habilitados' => [],
            ]);

            // Crear sucursal principal
            Sucursal::create([
                'clinica_id' => $nuevoConsultorio->id,
                'nombre' => $consultorioData['nombre'],
                'es_principal' => true,
                'activa' => true,
                'direccion' => $consultorioData['direccion'] ?? null,
                'telefono' => $consultorioData['telefono'] ?? null,
            ]);

            // Vincular al usuario como propietario
            $user->clinicas()->attach($nuevoConsultorio->id, [
                'rol_en_clinica' => 'propietario',
                'activa' => true,
            ]);

            // Incrementar contador de consultorios adicionales en la clínica principal
            $clinicaPrincipal = Clinica::where('propietario_user_id', $user->id)
                ->where('plan_type', '!=', 'consultorio-adicional')
                ->first();
            
            if ($clinicaPrincipal) {
                $clinicaPrincipal->increment('consultorios_adicionales');
            }

            // Registrar pago
            Payment::create([
                'clinica_id' => $nuevoConsultorio->id,
                'user_id' => $user->id,
                'amount' => $session->amount_total / 100,
                'currency' => strtoupper($session->currency),
                'status' => 'completed',
                'payment_method' => 'stripe',
                'stripe_payment_id' => $session->payment_intent,
                'metadata' => [
                    'type' => 'consultorio_adicional',
                    'session_id' => $session->id,
                ],
            ]);

            DB::commit();

            Log::info("Consultorio adicional creado para usuario: {$user->email}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error fulfilling consultorio adicional: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Manejar cancelación de suscripción
     */
    private function handleSubscriptionCancellation($subscription): void
    {
        try {
            $clinica = Clinica::where('stripe_subscription_id', $subscription->id)->first();
            
            if ($clinica) {
                $clinica->update([
                    'activa' => false,
                    'pagado' => false,
                ]);

                Log::info("Suscripción cancelada para clínica: {$clinica->nombre}");
            }
        } catch (\Exception $e) {
            Log::error('Error handling subscription cancellation: ' . $e->getMessage());
        }
    }

    /**
     * Verificar sesión de pago y retornar token de autenticación
     */
    public function verifySession(Request $request, $sessionId): JsonResponse
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'El pago aún no ha sido completado'
                ], 400);
            }

            // Buscar usuario por el email en los metadatos
            $metadata = $session->metadata;
            $userData = json_decode($metadata->user_data, true);
            
            $user = User::where('email', $userData['email'])
                ->with(['clinica', 'clinicaActiva', 'sucursal'])
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado. El webhook aún no ha procesado el pago.'
                ], 404);
            }

            // Crear token de autenticación
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user,
                'message' => '¡Bienvenido! Tu consultorio ha sido activado exitosamente.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error verifying session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar la sesión de pago'
            ], 500);
        }
    }

    /**
     * Obtener todos los consultorios del usuario actual
     */
    public function listUserConsultorios(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Consultorio principal (incluido en suscripción)
            $consultorioPrincipal = $user->clinica;
            
            // Consultorios adicionales (add-ons)
            $consultoriosAdicionales = $user->consultorioAddons()
                ->with('clinica')
                ->where('is_active', true)
                ->get();

            return response()->json([
                'success' => true,
                'consultorio_principal' => $consultorioPrincipal,
                'consultorios_adicionales' => $consultoriosAdicionales->map(function($addon) {
                    return [
                        'id' => $addon->id,
                        'clinica' => $addon->clinica,
                        'billing_cycle' => $addon->billing_cycle,
                        'price' => $addon->getPrice(),
                        'expires_at' => $addon->expires_at,
                        'is_active' => $addon->isActive()
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Error listing consultorios: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener consultorios'
            ], 500);
        }
    }

    /**
     * Comprar un consultorio adicional
     */
    public function purchaseAdditionalConsultorio(Request $request): JsonResponse
    {
        $request->validate([
            'billing_cycle' => 'required|in:mensual,anual',
            'consultorio_nombre' => 'required|string|max:255',
            'tipo_clinica' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500'
        ]);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $user = $request->user();
            $billingCycle = $request->billing_cycle;
            
            // Precios para consultorios adicionales
            $priceMonthly = 299.00;
            $priceYearly = 2990.00;
            
            $price = $billingCycle === 'anual' ? $priceYearly : $priceMonthly;
            
            // Crear sesión de checkout de Stripe
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'mxn',
                        'product_data' => [
                            'name' => 'Consultorio Adicional',
                            'description' => 'Agrega otro consultorio a tu suscripción'
                        ],
                        'unit_amount' => $price * 100, // Stripe usa centavos
                        'recurring' => [
                            'interval' => $billingCycle === 'anual' ? 'year' : 'month'
                        ]
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => config('app.frontend_url') . '/settings/consultorios?addon_success=true&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.frontend_url') . '/settings/consultorios?addon_cancelled=true',
                'metadata' => [
                    'type' => 'consultorio_addon',
                    'user_id' => $user->id,
                    'billing_cycle' => $billingCycle,
                    'consultorio_nombre' => $request->consultorio_nombre,
                    'tipo_clinica' => $request->tipo_clinica,
                    'telefono' => $request->telefono ?? '',
                    'direccion' => $request->direccion ?? ''
                ]
            ]);

            return response()->json([
                'success' => true,
                'checkout_url' => $session->url,
                'session_id' => $session->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating addon checkout: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear sesión de pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar suscripción de consultorio adicional
     */
    public function cancelConsultorioAddon(Request $request, $addonId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $addon = $user->consultorioAddons()->findOrFail($addonId);
            
            if (!$addon->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este consultorio adicional ya está inactivo'
                ], 400);
            }

            // Cancelar suscripción en Stripe si existe
            if ($addon->stripe_subscription_id) {
                Stripe::setApiKey(config('services.stripe.secret'));
                try {
                    $subscription = \Stripe\Subscription::retrieve($addon->stripe_subscription_id);
                    $subscription->cancel();
                } catch (\Exception $e) {
                    Log::warning('Error cancelling Stripe subscription: ' . $e->getMessage());
                }
            }

            // Marcar como inactivo
            $addon->is_active = false;
            $addon->expires_at = now();
            $addon->save();

            return response()->json([
                'success' => true,
                'message' => 'Consultorio adicional cancelado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error cancelling addon: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar consultorio adicional'
            ], 500);
        }
    }

    /**
     * Obtener uso actual de consultorios del usuario
     */
    public function getConsultorioUsage(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Contar consultorios privados creados por el usuario
            $consultoriosCreados = $user->clinicas()
                ->wherePivot('rol_en_clinica', 'propietario')
                ->where('es_consultorio_privado', true)
                ->count();

            // El límite es 1 consultorio base + los adicionales permitidos
            $limiteConsultorios = 1 + ($user->consultorios_adicionales_permitidos ?? 0);

            return response()->json([
                'used' => $consultoriosCreados,
                'allowed' => $limiteConsultorios,
                'available' => max(0, $limiteConsultorios - $consultoriosCreados)
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting consultorio usage: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener uso de consultorios'
            ], 500);
        }
    }
}
