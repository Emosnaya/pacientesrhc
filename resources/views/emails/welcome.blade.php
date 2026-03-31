<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .header img {
            height: 72px;
            width: auto;
            max-width: 520px;
            object-fit: contain;
            margin-bottom: 16px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
        }
        .content h2 {
            color: #667eea;
            margin-top: 0;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box h3 {
            margin-top: 0;
            color: #667eea;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .features {
            margin: 20px 0;
        }
        .features li {
            margin: 10px 0;
            padding-left: 25px;
            position: relative;
        }
        .features li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #667eea;
            font-weight: bold;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @php
                $lynkamedSrc = \App\Helpers\EmailHelper::embedMailImage($message ?? null, \App\Helpers\EmailHelper::getLynkamedLogoPath());
            @endphp
            @if($lynkamedSrc)
                <img src="{{ $lynkamedSrc }}" alt="Lynkamed">
            @endif
            <h1>🎉 ¡Bienvenido a {{ config('app.name', 'Lynkamed') }}!</h1>
        </div>
        
        <div class="content">
            <h2>Hola Dr. {{ $user->nombre }} {{ $user->apellidoPat }},</h2>
            
            <p>¡Felicidades! Tu consultorio <strong>{{ $consultorio->nombre }}</strong> ha sido activado exitosamente.</p>
            
            <div class="info-box">
                <h3>📋 Detalles de tu suscripción</h3>
                <p><strong>Plan:</strong> {{ $plan->name }}</p>
                <p><strong>Ciclo de facturación:</strong> {{ ucfirst($consultorio->billing_cycle) }}</p>
                <p><strong>Próxima facturación:</strong> {{ $consultorio->next_billing_date->format('d/m/Y') }}</p>
            </div>

            <p>Ya puedes comenzar a usar todas las funciones de tu consultorio:</p>

            <ul class="features">
                @foreach($plan->features as $feature)
                <li>{{ $feature }}</li>
                @endforeach
            </ul>

            <center>
                <a href="{{ config('app.frontend_url', env('FRONTEND_URL')) }}" class="button">
                    Acceder a mi consultorio
                </a>
            </center>

            <div class="info-box" style="margin-top: 30px;">
                <h3>🚀 Próximos pasos</h3>
                <ol>
                    <li>Completa tu perfil profesional</li>
                    <li>Configura tu horario de consultas</li>
                    <li>Invita a tu equipo de trabajo (si aplica)</li>
                    <li>Registra tu primer paciente</li>
                </ol>
            </div>

            <p>Si tienes alguna pregunta o necesitas ayuda, nuestro equipo de soporte está disponible para asistirte.</p>

            <p>¡Bienvenido a bordo!<br>
            El equipo de {{ config('app.name') }}</p>
        </div>

        <div class="footer">
            <p>Este correo fue enviado a {{ $user->email }}</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #dee2e6; font-size: 11px; color: #94a3b8;">
                <span>Powered by</span> <strong style="color: #667eea;">Lynkamed</strong>
            </div>
        </div>
    </div>
</body>
</html>
