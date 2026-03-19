<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Cita - {{ $clinica->nombre ?? 'Sistema Médico' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            background-color: #f1f5f9;
            padding: 40px 20px;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }
        .header {
            background: #0A1628;
            padding: 32px 30px;
            text-align: center;
        }
        .header img {
            max-width: 160px;
            max-height: 70px;
            object-fit: contain;
            background: white;
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .header h1 {
            color: white;
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 6px 0;
        }
        .header p {
            color: #94a3b8;
            font-size: 13px;
            margin: 0;
        }
        .content {
            padding: 32px 30px;
        }
        .content h2 {
            font-size: 20px;
            font-weight: 700;
            color: #0A1628;
            margin-bottom: 20px;
        }
        .content p {
            color: #475569;
            font-size: 15px;
            line-height: 1.75;
            margin-bottom: 16px;
        }
        .content strong {
            color: #1e293b;
            font-weight: 600;
        }
        .appointment-details {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #0A1628;
            border-radius: 8px;
            padding: 22px 24px;
            margin: 24px 0;
        }
        .appointment-details h3 {
            color: #0A1628;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .detail-row {
            display: flex;
            align-items: flex-start;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .detail-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .detail-label {
            font-weight: 700;
            min-width: 130px;
            color: #64748b;
            font-size: 13px;
        }
        .detail-value {
            flex: 1;
            color: #1e293b;
            font-size: 14px;
        }
        .reminder {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-left: 4px solid #0d9488;
            border-radius: 6px;
            padding: 18px 20px;
            margin: 24px 0;
        }
        .reminder strong {
            color: #0d9488;
            font-size: 14px;
            font-weight: 700;
            display: block;
            margin-bottom: 10px;
        }
        .reminder ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .reminder ul li {
            color: #334155;
            padding: 5px 0 5px 16px;
            position: relative;
            font-size: 14px;
            line-height: 1.6;
        }
        .reminder ul li::before {
            content: '-';
            position: absolute;
            left: 0;
            color: #0d9488;
            font-weight: bold;
        }
        .footer {
            background: #f8fafc;
            border-top: 2px solid #0A1628;
            padding: 24px 30px;
            text-align: center;
        }
        .footer p {
            color: #64748b;
            font-size: 13px;
            line-height: 1.8;
            margin: 0;
        }
        .footer strong {
            color: #0A1628;
            font-size: 14px;
            display: block;
            margin-bottom: 6px;
        }
        @media only screen and (max-width: 600px) {
            body { padding: 16px 10px; }
            .header { padding: 24px 20px; }
            .content { padding: 24px 20px; }
            .detail-row { flex-direction: column; }
            .detail-label { min-width: auto; margin-bottom: 4px; }
            .footer { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <img src="{{ $clinica->logo}}" alt="{{ $clinica->nombre ?? 'Clínica' }} Logo">
            <h1>Nueva Cita Programada</h1>
            <p>{{ $clinica->nombre}}</p>
        </div>
        
        <div class="content">
            <h2>¡Cita Programada Exitosamente!</h2>
            
            <p>Hola <strong>{{ $recipientName }}</strong>,</p>
            
            <p>Se ha programado una nueva cita para el paciente <strong>{{ $paciente->nombre }} {{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }}</strong>.</p>
            
            <div class="appointment-details">
                <h3>Detalles de la Cita</h3>
                <div class="detail-row">
                    <div class="detail-label">Paciente:</div>
                    <div class="detail-value">{{ $paciente->nombre }} {{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Fecha:</div>
                    <div class="detail-value">{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Hora:</div>
                    <div class="detail-value">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Tipo:</div>
                    <div class="detail-value">{{ $cita->tipo }}</div>
                </div>
                @if($cita->observaciones)
                <div class="detail-row">
                    <div class="detail-label">Observaciones:</div>
                    <div class="detail-value">{{ $cita->observaciones }}</div>
                </div>
                @endif
            </div>
            
            <div class="reminder">
                <strong>Recordatorio Importante:</strong>
                <ul>
                    <li>Por favor llegue 15 minutos antes de su cita</li>
                    <li>Traiga una identificación oficial</li>
                    <li>Si necesita reprogramar o cancelar, contacte con anticipación</li>
                </ul>
            </div>
            
            <p>Si tienes alguna pregunta sobre esta cita, no dudes en contactarnos.</p>
        </div>
        
        <div class="footer">
            <p>
                <strong>{{ $clinica->nombre ?? 'Clínica Médica' }}</strong><br>
                @if($clinica->telefono ?? null)Tel: {{ $clinica->telefono }}<br>@endif
                @if($clinica->email ?? null)Email: {{ $clinica->email }}<br>@endif
                @if($clinica->direccion ?? null){{ $clinica->direccion }}@endif
            </p>
        </div>
    </div>
</body>
</html>
