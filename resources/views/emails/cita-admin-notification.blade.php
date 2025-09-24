<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Cita Programada - CERCAP</title>
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
            width: 150px;
            color: #555;
        }
        .detail-value {
            flex: 1;
        }
        .patient-details {
            background-color: #e8f4fd;
            border: 1px solid #bee5eb;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .patient-details h3 {
            color: #0c5460;
            margin-top: 0;
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
