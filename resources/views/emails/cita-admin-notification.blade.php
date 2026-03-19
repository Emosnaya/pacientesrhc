<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Cita Programada - CERCAP</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            background-color: #f1f5f9;
            padding: 40px 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }
        .header {
            background: #0A1628;
            padding: 28px 30px;
            text-align: center;
        }
        .header img {
            max-width: 160px;
            max-height: 60px;
            object-fit: contain;
            background: white;
            padding: 8px 14px;
            border-radius: 6px;
            margin-bottom: 14px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .header h1 {
            color: white;
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 4px 0;
        }
        .header p {
            color: #94a3b8;
            font-size: 13px;
            margin: 0;
        }
        .content {
            padding: 28px 30px;
        }
        .content h2 {
            color: #0A1628;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .content p {
            color: #475569;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 14px;
        }
        .appointment-details {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #0A1628;
            border-radius: 6px;
            padding: 18px 20px;
            margin: 20px 0;
        }
        .appointment-details h3 {
            color: #0A1628;
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 14px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .patient-details {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid #1d4ed8;
            border-radius: 6px;
            padding: 18px 20px;
            margin: 20px 0;
        }
        .patient-details h3 {
            color: #1e40af;
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 14px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid #bfdbfe;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .detail-row {
            display: flex;
            align-items: flex-start;
            padding: 7px 0;
            border-bottom: 1px solid rgba(0,0,0,0.04);
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label {
            font-weight: 700;
            min-width: 150px;
            color: #64748b;
            font-size: 13px;
        }
        .detail-value {
            flex: 1;
            color: #1e293b;
            font-size: 13px;
        }
        .reminder {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #0d9488;
            border-radius: 6px;
            padding: 14px 16px;
            margin: 18px 0;
            color: #166534;
            font-size: 14px;
        }
        .footer {
            background: #f8fafc;
            border-top: 2px solid #0A1628;
            padding: 20px 30px;
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
            font-weight: 700;
        }
        @media only screen and (max-width: 600px) {
            body { padding: 16px 10px; }
            .header, .content, .footer { padding-left: 16px; padding-right: 16px; }
            .detail-row { flex-direction: column; }
            .detail-label { min-width: auto; margin-bottom: 2px; }
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
                <div class="detail-row">
                    <div class="detail-label">Estado:</div>
                    <div class="detail-value">{{ ucfirst($cita->estado) }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Primera Vez:</div>
                    <div class="detail-value">{{ $cita->primera_vez ? 'Sí' : 'No' }}</div>
                </div>
                @if($cita->notas)
                <div class="detail-row">
                    <div class="detail-label">Notas:</div>
                    <div class="detail-value">{{ $cita->notas }}</div>
                </div>
                @endif
            </div>

            <div class="patient-details">
                <h3>Información del Paciente</h3>
                <div class="detail-row">
                    <div class="detail-label">Registro:</div>
                    <div class="detail-value">{{ $paciente->registro }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Teléfono:</div>
                    <div class="detail-value">{{ $paciente->telefono }}</div>
                </div>
                @if($paciente->email)
                <div class="detail-row">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value">{{ $paciente->email }}</div>
                </div>
                @endif
                <div class="detail-row">
                    <div class="detail-label">Fecha de Nacimiento:</div>
                    <div class="detail-value">{{ \Carbon\Carbon::parse($paciente->fechaNacimiento)->format('d/m/Y') }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Edad:</div>
                    <div class="detail-value">{{ $paciente->edad }} años</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Género:</div>
                    <div class="detail-value">{{ $paciente->genero ? 'Femenino' : 'Masculino' }}</div>
                </div>
                @if($paciente->diagnostico)
                <div class="detail-row">
                    <div class="detail-label">Diagnóstico:</div>
                    <div class="detail-value">{{ $paciente->diagnostico }}</div>
                </div>
                @endif
                @if($paciente->medicamentos)
                <div class="detail-row">
                    <div class="detail-label">Medicamentos:</div>
                    <div class="detail-value">{{ $paciente->medicamentos }}</div>
                </div>
                @endif
            </div>
            
            <p>Si necesitas modificar o cancelar esta cita, puedes hacerlo desde el sistema de gestión.</p>
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
