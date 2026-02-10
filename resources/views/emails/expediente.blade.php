<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Expediente Médico - {{ $clinica->nombre ?? 'Clínica' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #255FA5 0%, #1e4a7f 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header img {
            max-height: 80px;
            max-width: 200px;
            object-fit: contain;
            margin-bottom: 15px;
            background-color: white;
            padding: 10px;
            border-radius: 8px;
        }
        .header h1 {
            margin: 10px 0 5px 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .patient-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #255FA5;
        }
        .patient-info h3 {
            margin-top: 0;
            color: #255FA5;
        }
        .patient-info p {
            margin: 8px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        .footer p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        .clinic-info {
            font-size: 12px;
            color: #888;
            margin-top: 10px;
        }
        strong {
            color: #255FA5;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            @if($clinica && $clinica->logo)
                <img src="{{ $clinica->logo }}" alt="{{ $clinica->nombre }} Logo" class="logo">
            @endif
            <h1>{{ $tipoExpedienteNombre ?? 'Expediente Médico' }}</h1>
            <p>{{ $clinica->nombre ?? 'Clínica Médica' }}</p>
        </div>
        
        <div class="content">
            <p>Estimado/a,</p>
            
            <p>{{ $mensaje }}</p>
            
            <div class="patient-info">
                <h3>📋 Información del Paciente</h3>
                <p><strong>Nombre:</strong> {{ $paciente->nombre }} {{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }}</p>
                <p><strong>Registro:</strong> {{ $paciente->registro ?? 'N/A' }}</p>
                @if($paciente->fechaNacimiento)
                    <p><strong>Fecha de Nacimiento:</strong> {{ \Carbon\Carbon::parse($paciente->fechaNacimiento)->format('d/m/Y') }}</p>
                @endif
                <p><strong>Edad:</strong> {{ $paciente->edad ?? 'N/A' }} años</p>
                @if($paciente->diagnostico)
                    <p><strong>Diagnóstico:</strong> {{ $paciente->diagnostico }}</p>
                @endif
            </div>
            
            <p>📎 El expediente se encuentra adjunto a este correo en formato PDF.</p>
            
            <p>Si tiene alguna pregunta o necesita información adicional, no dude en contactarnos.</p>
            
            <p>Saludos cordiales,<br>
            <strong>{{ $clinica->nombre ?? 'Clínica Médica' }}</strong></p>
        </div>
        
        <div class="footer">
            <p><strong>{{ $clinica->nombre ?? 'Clínica Médica' }}</strong></p>
            <div class="clinic-info">
                @if($clinica && $clinica->telefono)
                    <p>📞 {{ $clinica->telefono }}</p>
                @endif
                @if($clinica && $clinica->email)
                    <p>📧 {{ $clinica->email }}</p>
                @endif
                @if($clinica && $clinica->direccion)
                    <p>📍 {{ $clinica->direccion }}</p>
                @endif
            </div>
            <p style="margin-top: 15px; font-size: 11px; color: #999;">
                Este es un correo automático, por favor no responder directamente a este mensaje.
            </p>
        </div>
    </div>
</body>
</html>
