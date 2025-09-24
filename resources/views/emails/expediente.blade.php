<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Expediente Médico - CERCAP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #255FA5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .patient-info {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #255FA5;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background-color: #e9ecef;
            border-radius: 8px;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
        .header img {
            display: block;
            margin: 0 auto 15px auto;
            max-width: 200px;
            height: 90px;
            object-fit: contain;
            display: flex ;
            justify-content: between;
            align-items: between;
    }
    </style>
</head>
<body>
    <div class="header">
        <img src="https://pacientesrhc.com/img/logo.png" alt="CERCAP Logo" style="height: 90px; margin-bottom: 15px;">
        <h1>{{ $tipoExpedienteNombre ?? 'Expediente Médico' }}</h1>
        <p>Rehabilitación CardioPulmonar</p>
    </div>
    
    <div class="content">
        <p>Estimado/a,</p>
        
        <p>{{ $mensaje }}</p>
        
        <div class="patient-info">
            <h3>Información del Paciente</h3>
            <p><strong>Nombre:</strong> {{ $paciente->nombre }} {{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }}</p>
            <p><strong>Registro:</strong> {{ $paciente->registro ?? 'N/A' }}</p>
            <p><strong>Fecha de Nacimiento:</strong> {{ $paciente->fechaNacimiento ?? 'N/A' }}</p>
            <p><strong>Edad:</strong> {{ $paciente->edad ?? 'N/A' }} años</p>
            @if($paciente->diagnostico)
                <p><strong>Diagnóstico:</strong> {{ $paciente->diagnostico }}</p>
            @endif
        </div>
        
        <p>El expediente se encuentra adjunto a este correo en formato PDF.</p>
        
        <p>Si tiene alguna pregunta o necesita información adicional, no dude en contactarnos.</p>
        
        <p>Saludos cordiales,<br>
        <strong>Equipo CERCAP</strong></p>
    </div>
    
    <div class="footer">
        <p><strong>CERCAP</strong><br>
        Clinica Reabilitación Cardiopulmonar<br>
        Tel: 5526255547<br>
        Email: cerc</p>
    </div>
</body>
</html>
