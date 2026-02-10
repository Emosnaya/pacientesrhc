<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Cita - {{ $clinica->nombre ?? 'Sistema Médico' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }
        
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        
        .header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, 
                #667eea 0%, 
                #764ba2 25%, 
                #f093fb 50%, 
                #764ba2 75%, 
                #667eea 100%);
        }
        
        .header img {
            max-width: 180px;
            height: auto;
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: white;
            font-size: 28px;
            font-weight: 700;
            margin: 15px 0 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .header p {
            color: rgba(255, 255, 255, 0.95);
            font-size: 16px;
            font-weight: 500;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .content h2 {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 25px;
        }
        
        .content p {
            color: #555;
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 20px;
        }
        
        .content strong {
            color: #2c3e50;
            font-weight: 600;
        }
        
        .appointment-details {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .appointment-details h3 {
            color: #2c3e50;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid rgba(102, 126, 234, 0.3);
        }
        
        .detail-row {
            display: flex;
            align-items: flex-start;
            margin-bottom: 14px;
            padding: 8px 0;
        }
        
        .detail-row:last-child {
            margin-bottom: 0;
        }
        
        .detail-label {
            font-weight: 700;
            min-width: 130px;
            color: #667eea;
            font-size: 15px;
        }
        
        .detail-value {
            flex: 1;
            color: #2c3e50;
            font-size: 15px;
            font-weight: 500;
        }
        
        .reminder {
            background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);
            border-left: 4px solid #00acc1;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        .reminder strong {
            color: #00838f;
            font-size: 17px;
            display: block;
            margin-bottom: 12px;
        }
        
        .reminder ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .reminder ul li {
            color: #00695c;
            padding: 6px 0;
            padding-left: 25px;
            position: relative;
            line-height: 1.6;
        }
        
        .reminder ul li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #00acc1;
            font-weight: bold;
            font-size: 16px;
        }
        
        .footer {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            padding: 30px;
            text-align: center;
            border-top: 3px solid #667eea;
        }
        
        .footer p {
            color: #5a6c7d;
            font-size: 14px;
            line-height: 1.8;
            margin: 0;
        }
        
        .footer strong {
            color: #2c3e50;
            font-size: 16px;
            display: block;
            margin-bottom: 8px;
        }
        
        /* Responsive Design */
        @media only screen and (max-width: 600px) {
            body {
                padding: 20px 10px;
            }
            
            .email-wrapper {
                border-radius: 12px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .content h2 {
                font-size: 20px;
            }
            
            .appointment-details {
                padding: 20px;
            }
            
            .detail-row {
                flex-direction: column;
            }
            
            .detail-label {
                min-width: auto;
                margin-bottom: 4px;
            }
            
            .footer {
                padding: 25px 20px;
            }
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
                <h3>📋 Detalles de la Cita</h3>
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
                <strong>📌 Recordatorio Importante:</strong>
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
