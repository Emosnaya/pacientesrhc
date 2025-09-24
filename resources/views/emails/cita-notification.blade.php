<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Cita - CERCAP</title>
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
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header img {
            max-width: 200px;
            height: 90px;
            object-fit: contain;
        }
        .header h1 {
            color: #2c3e50;
            margin: 15px 0 10px 0;
        }
        .header p {
            color: #7f8c8d;
            margin: 0;
        }
        .content {
            margin-bottom: 30px;
        }
        .content h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .content p {
            margin-bottom: 15px;
            line-height: 1.6;
        }
        .appointment-details {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .appointment-details h3 {
            color: #2c3e50;
            margin-top: 0;
        }
        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            width: 120px;
            color: #555;
        }
        .detail-value {
            flex: 1;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
            color: #7f8c8d;
            font-size: 14px;
        }
        .reminder {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://pacientesrhc.com/img/logo.png" alt="CERCAP Logo">
            <h1>Nueva Cita Programada</h1>
            <p>Clínica de Rehabilitación Cardiopulmonar</p>
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
                <strong>Recordatorio:</strong><br>
                • Por favor llegue 15 minutos antes de su cita.<br>
                • Traiga una identificación oficial.<br>
                • Si necesita reprogramar o cancelar, contacte con anticipación.
            </div>
            
            <p>Si tienes alguna pregunta sobre esta cita, no dudes en contactarnos.</p>
        </div>
        
        <div class="footer">
            <p><strong>CERCAP</strong><br>
            Clínica de Rehabilitación Cardiopulmonar<br>
            Tel: 5526255547 / 5526255548<br>
            Email: cercap.cardiopulmonar@gmail.com<br>
            wwww.cercap.mx
        </p>
        </div>
    </div>
</body>
</html>
