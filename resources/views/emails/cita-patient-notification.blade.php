<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Cita - CERCAP</title>
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
        .appointment-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        .appointment-card h3 {
            margin-top: 0;
            font-size: 24px;
        }
        .appointment-info {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .info-item {
            text-align: center;
            margin: 10px;
        }
        .info-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 18px;
            font-weight: bold;
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
        .contact-info {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://pacientesrhc.com/img/logo.png" alt="CERCAP Logo">
            <h1>Confirmación de Cita</h1>
            <p>Clínica de Rehabilitación Cardiopulmonar</p>
        </div>
        
        <div class="content">
            <h2>¡Su cita ha sido confirmada!</h2>
            
            <p>Estimado/a <strong>{{ $paciente->nombre }} {{ $paciente->apellidoPat }}</strong>,</p>
            
            <p>Nos complace confirmar que su cita ha sido programada exitosamente. A continuación encontrará todos los detalles:</p>
            
            <div class="appointment-card">
                <h3>📅 Su Cita Médica</h3>
                <div class="appointment-info">
                    <div class="info-item">
                        <div class="info-label">Fecha</div>
                        <div class="info-value">{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Hora</div>
                        <div class="info-value">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tipo</div>
                        <div class="info-value">{{ $cita->tipo }}</div>
                    </div>
                </div>
            </div>
            
            <div class="reminder">
                <strong>📋 Recordatorios Importantes:</strong><br>
                • Por favor llegue 15 minutos antes de su cita.<br>
                • Traiga una identificación oficial (INE, pasaporte, etc.).<br>
                • Si es su primera consulta, traiga estudios médicos previos si los tiene.<br>
                • Si necesita reprogramar o cancelar, contacte con al menos 24 horas de anticipación.
            </div>
            
            <p>Esperamos verlo pronto. Si tiene alguna pregunta, no dude en contactarnos.</p>
            
            <p><strong>¡Gracias por confiar en nosotros para su cuidado médico!</strong></p>
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
