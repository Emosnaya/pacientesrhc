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
     * Verificar si un email ya está registrado
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'success' => true,
            'exists' => $exists,
            'message' => $exists 
                ? 'Este correo ya está registrado. Por favor usa otro o inicia sesión.' 
                : 'Correo disponible'
        ]);
    }

    /**
     * Validar código promocional
     */
    public function validatePromoCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:mensual,anual'
        ]);

        $code = strtoupper(trim($request->code));
        
        // Lista de códigos promocionales válidos
        $promoCodes = [
            'LANZAMIENTO2026' => [
                'type' => 'percentage',
                'value' => 20,
                'message' => '¡Código válido! 20% de descuento aplicado',
                'valid_until' => '2026-12-31'
            ],
            'PROMO50' => [
                'type' => 'fixed',
                'value' => 50,
                'message' => '¡Código válido! $50 MXN de descuento aplicado',
                'valid_until' => null
            ],
        ];

        if (!isset($promoCodes[$code])) {
            return response()->json([
                'success' => false,
                'message' => 'Código promocional inválido'
            ], 422);
        }

        $promo = $promoCodes[$code];

        // Verificar fecha de expiración
        if ($promo['valid_until'] && now()->gt($promo['valid_until'])) {
            return response()->json([
                'success' => false,
                'message' => 'Este código promocional ha expirado'
            ], 422);
        }

        $plan = SubscriptionPlan::findOrFail($request->plan_id);
        $price = $request->billing_cycle === 'anual' 
            ? $plan->price_yearly 
            : $plan->price_monthly;

        // Calcular descuento
        $discountAmount = $promo['type'] === 'percentage'
            ? ($price * $promo['value'] / 100)
            : $promo['value'];

        return response()->json([
            'success' => true,
            'code' => $code,
            'message' => $promo['message'],
            'discount_type' => $promo['type'],
            'discount_value' => $promo['value'],
            'discount_amount' => round($discountAmount, 2),
            'original_price' => $price,
            'final_price' => round($price - $discountAmount, 2)
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
            'user_data.cedula' => ['required', 'string', 'regex:/^[0-9]{7,8}$/'],
            'user_data.password' => 'required|string|min:6',
            'consultorio_data' => 'required|array',
            'consultorio_data.nombre' => 'required|string|max:255',
            'consultorio_data.tipo_clinica' => 'required|string',
        ], [
            'user_data.cedula.regex' => 'La cédula profesional debe contener entre 7 y 8 dígitos numéricos',
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
                
                // Verificar tipo de operación
                $metadata = $session->metadata;
                if (isset($metadata->type)) {
                    switch ($metadata->type) {
                        case 'consultorio_adicional':
                            $this->fulfillConsultorioAdicional($session);
                            break;
                        case 'consultorio_renewal':
                            $this->fulfillConsultorioRenewal($session);
                            break;
                        default:
                            $this->fulfillOrder($session);
                    }
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
            $user->clinicas()->attach($consultorio->id, User::pivotPropietarioConsultorio());

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
            $user->clinicas()->attach($nuevoConsultorio->id, User::pivotPropietarioConsultorio());

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
     * Procesar RENOVACIÓN de suscripción de consultorio después de pago exitoso
     */
    private function fulfillConsultorioRenewal($session): void
    {
        try {
            $metadata = $session->metadata;
            $clinicaId = $metadata->clinica_id ?? null;
            $userId = $metadata->user_id ?? null;
            $billingCycle = $metadata->billing_cycle ?? 'mensual';
            $duracionDias = $metadata->duracion_dias ?? ($billingCycle === 'anual' ? 365 : 30);

            if (!$clinicaId) {
                Log::error('Renovación de consultorio: clinica_id no encontrado en metadata');
                return;
            }

            $clinica = Clinica::find($clinicaId);
            
            if (!$clinica) {
                Log::error("Renovación de consultorio: Clínica {$clinicaId} no encontrada");
                return;
            }

            DB::beginTransaction();

            // Calcular nueva fecha de vencimiento
            $fechaBase = $clinica->fecha_vencimiento && $clinica->fecha_vencimiento->greaterThan(now())
                ? $clinica->fecha_vencimiento
                : now();
            
            $nuevaFechaVencimiento = $fechaBase->addDays($duracionDias);

            // Actualizar clínica
            $clinica->update([
                'activa' => true,
                'pagado' => true,
                'fecha_vencimiento' => $nuevaFechaVencimiento,
                'stripe_subscription_id' => $session->subscription ?? $session->id,
                'billing_cycle' => $billingCycle,
            ]);

            // Si hay usuario propietario, actualizar también su suscripción
            if ($userId) {
                $user = User::find($userId);
                if ($user) {
                    $user->update([
                        'tiene_suscripcion_consultorio' => true,
                        'ciclo_facturacion' => $billingCycle,
                        'suscripcion_fin' => $nuevaFechaVencimiento,
                        'stripe_subscription_id' => $session->subscription ?? $session->id,
                    ]);
                }
            }

            // Registrar pago
            Payment::create([
                'clinica_id' => $clinica->id,
                'user_id' => $userId,
                'amount' => $session->amount_total / 100,
                'currency' => strtoupper($session->currency ?? 'mxn'),
                'status' => 'completed',
                'payment_method' => 'stripe',
                'stripe_payment_id' => $session->payment_intent ?? $session->id,
                'stripe_invoice_id' => $session->invoice ?? null,
                'metadata' => [
                    'type' => 'consultorio_renewal',
                    'billing_cycle' => $billingCycle,
                    'session_id' => $session->id,
                    'nueva_fecha_vencimiento' => $nuevaFechaVencimiento->format('Y-m-d'),
                ],
            ]);

            DB::commit();

            Log::info("Renovación de consultorio exitosa", [
                'clinica_id' => $clinica->id,
                'clinica_nombre' => $clinica->nombre,
                'nueva_fecha_vencimiento' => $nuevaFechaVencimiento->format('Y-m-d'),
                'billing_cycle' => $billingCycle,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error procesando renovación de consultorio: ' . $e->getMessage(), [
                'session_id' => $session->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
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
     * FALLBACK: Si el webhook no procesó el pago, lo procesamos aquí
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

            // FALLBACK: Si el usuario no existe después de 5 segundos, procesar manualmente
            if (!$user) {
                Log::warning('Webhook no procesó el pago, ejecutando fallback', [
                    'session_id' => $sessionId,
                    'email' => $userData['email']
                ]);

                try {
                    // Verificar que sea un registro nuevo (no renovación)
                    if ($metadata->type ?? 'consultorio_signup' === 'consultorio_signup' || !isset($metadata->type)) {
                        $this->fulfillOrder($session);
                        
                        // Intentar obtener el usuario recién creado
                        $user = User::where('email', $userData['email'])
                            ->with(['clinica', 'clinicaActiva', 'sucursal'])
                            ->first();
                    }
                } catch (\Exception $e) {
                    Log::error('Error en fallback de fulfillOrder: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Error procesando el registro. Por favor contacta a soporte con este código: ' . $sessionId
                    ], 500);
                }
            }

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo completar el registro. Por favor contacta a soporte con el código: ' . $sessionId
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

    /**
     * Verificar estado de suscripción del usuario actual
     * Usado para mostrar modal de renovación/contacto
     */
    public function checkUserSubscription(Request $request): JsonResponse
    {
        $user = $request->user();
        $clinicaId = $user->clinica_activa_id ?? $user->clinica_id;
        
        if (!$clinicaId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario sin clínica asignada',
            ], 400);
        }

        $clinica = Clinica::find($clinicaId);
        
        if (!$clinica) {
            return response()->json([
                'success' => false,
                'message' => 'Clínica no encontrada',
            ], 404);
        }

        $esConsultorio = $clinica->es_consultorio_privado;
        $fechaVencimiento = $clinica->fecha_vencimiento;
        $now = now();
        
        // Calcular estado
        $activa = $clinica->activa && $clinica->pagado;
        $diasRestantes = $fechaVencimiento ? $now->diffInDays($fechaVencimiento, false) : null;
        $vencida = $fechaVencimiento && $now->greaterThan($fechaVencimiento);
        
        // Para consultorios, verificar suscripción del propietario
        // SOLO si la fecha de vencimiento de la clínica no está en el futuro
        // (evitar falsos positivos cuando fecha_vencimiento > now pero tiene_suscripcion_consultorio = false)
        if ($esConsultorio && $clinica->propietario_user_id && !$vencida) {
            $propietario = $clinica->propietario ?? $user;
            // Solo marcar vencida si el propietario no tiene ni trial ni suscripción activa
            // y además la clínica no tiene fecha_vencimiento en el futuro
            if ($propietario && !$propietario->tieneSuscripcionConsultorioActiva()) {
                // Si la clínica tiene fecha_vencimiento válida en el futuro, respetarla
                if (!$fechaVencimiento || !$now->lessThan($fechaVencimiento)) {
                    $vencida = true;
                    $activa = false;
                }
            }
        }

        return response()->json([
            'success' => true,
            'subscription' => [
                'clinica_id' => $clinica->id,
                'clinica_nombre' => $clinica->nombre,
                'es_consultorio' => $esConsultorio,
                'plan' => $clinica->plan,
                'activa' => $activa && !$vencida,
                'vencida' => $vencida,
                'fecha_vencimiento' => $fechaVencimiento?->format('Y-m-d'),
                'dias_restantes' => $vencida ? null : $diasRestantes,
                'dias_vencida' => $vencida ? abs($diasRestantes) : null,
                'puede_renovar_online' => $esConsultorio,
                'tipo_renovacion' => $esConsultorio ? 'stripe' : 'contacto_comercial',
            ],
            'planes_disponibles' => $esConsultorio ? [
                'mensual' => [
                    'precio' => 1499,
                    'ciclo' => 'mensual',
                    'etiqueta' => 'Precio de lanzamiento',
                ],
                'anual' => [
                    'precio' => 14999,
                    'ciclo' => 'anual',
                    'ahorro' => 2989,
                    'etiqueta' => 'Precio de lanzamiento',
                ],
            ] : null,
        ]);
    }

    /**
     * Crear sesión de checkout de Stripe para RENOVAR suscripción de consultorio
     */
    public function createRenewCheckoutSession(Request $request): JsonResponse
    {
        $request->validate([
            'billing_cycle' => 'required|in:mensual,anual',
        ]);

        try {
            $user = $request->user();
            $clinicaId = $user->clinica_activa_id ?? $user->clinica_id;
            
            $clinica = Clinica::find($clinicaId);
            
            if (!$clinica) {
                return response()->json([
                    'success' => false,
                    'message' => 'Clínica no encontrada',
                ], 404);
            }

            if (!$clinica->es_consultorio_privado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los consultorios privados pueden renovar online. Las clínicas deben contactar al equipo comercial.',
                ], 400);
            }

            // Precios de renovación (alineados con landing)
            $precios = [
                'mensual' => 1499,
                'anual' => 14999,
            ];
            
            $precio = $precios[$request->billing_cycle];
            $duracion = $request->billing_cycle === 'anual' ? 365 : 30;

            Stripe::setApiKey(config('services.stripe.secret'));

            // Crear sesión de Stripe Checkout
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'mxn',
                        'product_data' => [
                            'name' => "Renovación Consultorio - " . ucfirst($request->billing_cycle),
                            'description' => "Renovación de suscripción para {$clinica->nombre}",
                        ],
                        'unit_amount' => round($precio * 100),
                        'recurring' => [
                            'interval' => $request->billing_cycle === 'anual' ? 'year' : 'month',
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => config('app.frontend_url') . '/suscripcion/renovacion-exitosa?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.frontend_url') . '/suscripcion?cancelado=1',
                'metadata' => [
                    'type' => 'consultorio_renewal',
                    'clinica_id' => $clinica->id,
                    'user_id' => $user->id,
                    'billing_cycle' => $request->billing_cycle,
                    'duracion_dias' => $duracion,
                ],
                'customer_email' => $user->email,
            ]);

            Log::info('Sesión de renovación de consultorio creada', [
                'session_id' => $session->id,
                'clinica_id' => $clinica->id,
                'user_id' => $user->id,
                'billing_cycle' => $request->billing_cycle,
            ]);

            return response()->json([
                'success' => true,
                'checkout_url' => $session->url,
                'session_id' => $session->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Error creando sesión de renovación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear sesión de pago: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // FREE TRIAL — registro sin pago
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Códigos de referido / ventas que extienden el trial a 30 días.
     * La clave es el código (en mayúsculas), el valor es un array con:
     *   - days   : días de prueba (default 15)
     *   - label  : mensaje de éxito
     */
    private function referralCodes(): array
    {
        return [
            'AMIGO30'    => ['days' => 30, 'label' => '¡Código de amigo aplicado! Tienes 30 días de prueba gratis.'],
            'VENTAS30'   => ['days' => 30, 'label' => '30 días especiales activados para ti.'],
            'DEMO2026'   => ['days' => 30, 'label' => '¡Código demo activado! 30 días de acceso completo.'],
            'BETA2026'   => ['days' => 30, 'label' => '¡Bienvenido beta tester! 30 días gratis.'],
            'LYNKA30'    => ['days' => 30, 'label' => '¡Código LynkaMed especial! 30 días de prueba.'],
        ];
    }

    /**
     * POST /api/registro/validate-referral
     * Valida un código de referido y retorna los días de prueba.
     */
    public function validateReferralCode(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);
        $code  = strtoupper(trim($request->code));
        $codes = $this->referralCodes();

        if (!isset($codes[$code])) {
            return response()->json(['success' => false, 'message' => 'Código no válido.'], 422);
        }

        return response()->json([
            'success' => true,
            'days'    => $codes[$code]['days'],
            'label'   => $codes[$code]['label'],
        ]);
    }

    /**
     * POST /api/registro/free-trial
     * Crea usuario + consultorio + sucursal con trial gratuito. No requiere Stripe.
     */
    public function startFreeTrial(Request $request): JsonResponse
    {
        $request->validate([
            'user_data'                     => 'required|array',
            'user_data.nombre'              => 'required|string|max:255',
            'user_data.apellidoPat'         => 'required|string|max:255',
            'user_data.apellidoMat'         => 'nullable|string|max:255',
            'user_data.email'               => 'required|email|unique:users,email',
            'user_data.cedula'              => ['required', 'string', 'regex:/^[0-9]{7,8}$/'],
            'user_data.password'            => 'required|string|min:6',
            'consultorio_data'              => 'required|array',
            'consultorio_data.nombre'       => 'required|string|max:255',
            'consultorio_data.tipo_clinica' => 'required|string',
            'consultorio_data.telefono'     => 'nullable|string',
            'consultorio_data.email'        => 'nullable|email',
            'consultorio_data.direccion'    => 'nullable|string',
            'referral_code'                 => 'nullable|string',
        ], [
            'user_data.cedula.regex' => 'La cédula profesional debe contener entre 7 y 8 dígitos numéricos.',
        ]);

        // Determinar días de trial
        $trialDays = 15;
        $referralMsg = null;
        if ($request->referral_code) {
            $code  = strtoupper(trim($request->referral_code));
            $codes = $this->referralCodes();
            if (isset($codes[$code])) {
                $trialDays   = $codes[$code]['days'];
                $referralMsg = $codes[$code]['label'];
            }
        }

        try {
            DB::beginTransaction();

            $ud = $request->user_data;
            $cd = $request->consultorio_data;

            // 1. Crear usuario
            $user = User::create([
                'nombre'           => $ud['nombre'],
                'apellidoPat'      => $ud['apellidoPat'],
                'apellidoMat'      => $ud['apellidoMat'] ?? null,
                'email'            => $ud['email'],
                'cedula'           => $ud['cedula'],
                'password'         => Hash::make($ud['password']),
                'isAdmin'          => true,
                'email_verified'   => true,
                'email_verified_at'=> now(),
                'imagen'           => 'perfiles/avatar-default.png',
                // Suscripción
                'tiene_suscripcion_consultorio' => true,
                'plan_consultorio'              => 'consultorio',
                'ciclo_facturacion'             => 'mensual',
                'trial_ends_at'                 => now()->addDays($trialDays),
            ]);

            // 2. Crear consultorio en modo trial
            $trialEndsAt = now()->addDays($trialDays);

            $consultorio = Clinica::create([
                'nombre'                      => $cd['nombre'],
                'tipo_clinica'                => $cd['tipo_clinica'],
                'email'                       => $cd['email'] ?? $user->email,
                'telefono'                    => $cd['telefono'] ?? null,
                'direccion'                   => $cd['direccion'] ?? null,
                'plan'                        => 'profesional',
                'plan_type'                   => 'profesional',
                'billing_cycle'               => 'mensual',
                'duration'                    => 'mensual',
                'pagado'                      => false,
                'activa'                      => true,
                'es_consultorio_privado'      => true,
                'propietario_user_id'         => $user->id,
                'permite_multiples_sucursales'=> false,
                'max_pacientes'               => 999999,
                'max_usuarios'               => 3,
                'max_sucursales'              => 1,
                'trial_ends_at'              => $trialEndsAt,
                'fecha_vencimiento'          => $trialEndsAt,
                'modulos_habilitados'        => [],
            ]);

            // 3. Crear sucursal principal
            $sucursal = Sucursal::create([
                'clinica_id' => $consultorio->id,
                'nombre'     => $cd['nombre'],
                'es_principal'=> true,
                'activa'     => true,
                'direccion'  => $cd['direccion'] ?? null,
                'telefono'   => $cd['telefono'] ?? null,
            ]);

            // 4. Vincular propietario
            $user->clinicas()->attach($consultorio->id, User::pivotPropietarioConsultorio());

            // 5. Activar workspace
            $user->update([
                'clinica_activa_id' => $consultorio->id,
                'sucursal_id'       => $sucursal->id,
            ]);

            DB::commit();

            // Generar token
            $token = $user->createToken('authToken')->plainTextToken;

            // Email de bienvenida (best-effort)
            try {
                Mail::to($user->email)->send(new \App\Mail\WelcomeMail($user, $consultorio, null));
            } catch (\Exception $e) {
                Log::warning('Error sending trial welcome email: ' . $e->getMessage());
            }

            Log::info("Trial gratuito activado para: {$user->email} ({$trialDays} días)");

            return response()->json([
                'success'        => true,
                'message'        => "¡Bienvenido! Tu prueba gratuita de {$trialDays} días ha comenzado.",
                'referral_msg'   => $referralMsg,
                'token'          => $token,
                'user'           => $user->fresh(),
                'trial_ends_at'  => $trialEndsAt->format('Y-m-d'),
                'trial_days'     => $trialDays,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error starting free trial: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear tu cuenta: ' . $e->getMessage(),
            ], 500);
        }
    }
}
