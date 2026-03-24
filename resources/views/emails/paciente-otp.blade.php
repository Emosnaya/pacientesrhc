<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Código de verificación</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .otp-code {
            background: #fff;
            border: 2px dashed #667eea;
            padding: 20px;
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #667eea;
            margin: 20px 0;
            border-radius: 8px;
        }
        .warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
        }
        .footer {
            background: #1f2937;
            color: #9ca3af;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            border-radius: 0 0 10px 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔐 Código de Verificación</h1>
    </div>
    
    <div class="content">
        <p>Hola <strong>{{ $paciente->nombre }}</strong>,</p>
        
        <p>La clínica <strong>{{ $clinica->nombre }}</strong> está solicitando vincular tu expediente médico a su sistema.</p>
        
        <p>Tu código de verificación es:</p>
        
        <div class="otp-code">
            {{ $otpCode }}
        </div>
        
        <div class="warning">
            ⚠️ <strong>Este código expira en 15 minutos.</strong><br>
            Si no solicitaste esta vinculación, ignora este correo. Tu información permanece segura.
        </div>
        
        <p>Al verificar este código, permites que <strong>{{ $clinica->nombre }}</strong> acceda a tu historial médico para brindarte una mejor atención.</p>
    </div>
    
    <div class="footer">
        <p>Este es un correo automático. Por favor no respondas a este mensaje.</p>
        <p>© {{ date('Y') }} PacientesRHC - Sistema de Gestión Médica</p>
    </div>
</body>
</html>
