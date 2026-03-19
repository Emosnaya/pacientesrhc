<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
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
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }
        .header {
            background: #0A1628;
            padding: 32px 30px;
            text-align: center;
            color: #ffffff;
        }
        .logo {
            max-width: 160px;
            max-height: 70px;
            object-fit: contain;
            margin-bottom: 16px;
            background-color: white;
            padding: 10px 16px;
            border-radius: 8px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            color: white;
        }
        .content {
            padding: 32px 35px;
        }
        .greeting {
            font-size: 20px;
            font-weight: 700;
            color: #0A1628;
            margin-bottom: 14px;
        }
        .intro {
            font-size: 15px;
            color: #475569;
            margin-bottom: 24px;
            line-height: 1.7;
        }
        .cita-details {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #0A1628;
            padding: 22px 24px;
            border-radius: 8px;
            margin: 24px 0;
        }
        .detail-row {
            display: flex;
            align-items: flex-start;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 700;
            color: #64748b;
            min-width: 130px;
            font-size: 13px;
        }
        .detail-value {
            color: #1e293b;
            flex: 1;
            font-size: 14px;
        }
        .alert {
            border-radius: 6px;
            padding: 14px 18px;
            margin: 20px 0;
            font-size: 14px;
        }
        .alert.success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #16a34a;
            color: #166534;
        }
        .alert.danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #dc2626;
            color: #991b1b;
        }
        .btn-calendar {
            display: inline-block;
            padding: 13px 28px;
            background: #1d4ed8;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 700;
            font-size: 15px;
            text-align: center;
            margin: 20px 0;
        }
        .info-box {
            margin-top: 24px;
            padding: 20px 22px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid #1d4ed8;
            border-radius: 6px;
        }
        .info-box-title {
            color: #1e40af;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .info-box-content {
            line-height: 1.9;
            color: #334155;
            font-size: 14px;
        }
        .recommendations {
            margin-top: 16px;
            padding: 16px 18px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid #f59e0b;
            border-radius: 6px;
        }
        .recommendations-title {
            color: #92400e;
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .recommendations ul {
            margin: 10px 0 0 18px;
            color: #78350f;
            font-size: 13px;
            line-height: 1.9;
        }
        .footer {
            background: #f8fafc;
            padding: 24px 30px;
            text-align: center;
            color: #64748b;
            font-size: 13px;
            border-top: 2px solid #0A1628;
        }
        .footer .clinica-name {
            font-weight: 700;
            color: #0A1628;
            font-size: 16px;
            margin-bottom: 12px;
        }
        .footer-contact {
            margin: 12px 0;
            line-height: 1.9;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            background: #0d9488;
            color: white;
        }
        @media only screen and (max-width: 600px) {
            body { padding: 16px 10px; }
            .content { padding: 24px 20px; }
            .detail-row { flex-direction: column; }
            .detail-label { min-width: auto; margin-bottom: 4px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header con logo -->
            <div class="header">
                @if($clinica && $clinica->logo)
                    <img src="{{ $clinica->logo }}" alt="{{ $clinicaDisplayName ?? $clinica->nombre }} Logo" class="logo">
                @endif
                <h1>{{ $subject }}</h1>
            </div>

            <!-- Contenido -->
            <div class="content">
                <div class="greeting">{{ $greeting }}</div>
                
                <p class="intro">{{ $introLine }}</p>

                @if($action === 'cancel')
                    <div class="alert danger">
                        <strong>Atención:</strong> La cita ha sido cancelada.
                        @if($isPatient ?? false)
                            <br>Si necesitas reagendar, por favor contáctanos.
                        @endif
                    </div>
                @else
                    <div class="alert success">
                        <strong>Confirmación:</strong> 
                        @if($isPatient ?? false)
                            Tu cita ha sido registrada exitosamente.
                        @else
                            No olvides revisar tu calendario para más detalles.
                        @endif
                    </div>
                @endif

                @if(!($isPatient ?? false))
                <div style="text-align: center;">
                    <a href="{{ $calendarUrl }}"  class="btn-calendar">
                        Ver mi Calendario
                    </a>
                </div>
                @endif

                <!-- Detalles de la cita -->
                <div class="cita-details">
                    <div class="detail-row">
                        <span class="detail-label">Paciente:</span>
                        <span class="detail-value">{{ $paciente->nombre }} {{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Fecha:</span>
                        <span class="detail-value">{{ $fechaFormateada }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Hora:</span>
                        <span class="detail-value">{{ $horaFormateada }}</span>
                    </div>
                    @if($cita->primera_vez)
                    <div class="detail-row">
                        <span class="detail-label">Primera vez:</span>
                        <span class="detail-value"><span class="badge">Primera Consulta</span></span>
                    </div>
                    @endif
                    @if($cita->notas)
                    <div class="detail-row">
                        <span class="detail-label">Notas:</span>
                        <span class="detail-value">{{ $cita->notas }}</span>
                    </div>
                    @endif
                </div>

                @if($isPatient ?? false)
                    <!-- Información para el paciente -->
                    <div class="info-box">
                        <div class="info-box-title">Ubicación</div>
                        <div class="info-box-content">
                            @php
                                $direccion = $sucursal->direccion ?? ($clinica->direccion ?? null);
                                $telefono = $sucursal->telefono ?? ($clinica->telefono ?? null);
                                $email = $sucursal->email ?? ($clinica->email ?? null);
                            @endphp
                            @if($direccion)
                                <div><strong>Dirección:</strong> {{ $direccion }}</div>
                            @endif
                            @if($telefono)
                                <div style="margin-top: 12px;"><strong>Teléfono:</strong> {{ $telefono }}</div>
                            @endif
                            @if($email)
                                <div><strong>Email:</strong> {{ $email }}</div>
                            @endif
                        </div>
                        @if($action !== 'cancel')
                        <div class="recommendations">
                            <div class="recommendations-title">Recomendaciones importantes:</div>
                            <ul>
                                <li>Llega 10 minutos antes de tu cita</li>
                                <li>Trae tu identificación oficial</li>
                                @if($cita->primera_vez)
                                <li>Primera consulta: trae estudios médicos previos si los tienes</li>
                                @endif
                                <li>Si no puedes asistir, avísanos con anticipación</li>
                            </ul>
                        </div>
                        @endif
                    </div>
                @else
                    <!-- Información de contacto del paciente para el doctor -->
                    @if($paciente->telefono || $paciente->email || $paciente->domicilio)
                    <div class="info-box">
                        <div class="info-box-title">Información de contacto del paciente:</div>
                        <div class="info-box-content">
                            @if($paciente->telefono)
                            <div>Tel: {{ $paciente->telefono }}</div>
                            @endif
                            @if($paciente->email)
                            <div>Email: {{ $paciente->email }}</div>
                            @endif
                            @if($paciente->domicilio)
                            <div style="margin-top: 8px;">{{ $paciente->domicilio }}</div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Información de la clínica/sucursal para el doctor -->
                    @php
                        $direccion = $sucursal->direccion ?? ($clinica->direccion ?? null);
                        $telefono = $sucursal->telefono ?? ($clinica->telefono ?? null);
                        $email = $sucursal->email ?? ($clinica->email ?? null);
                    @endphp
                    @if($direccion || $telefono || $email)
                    <div class="info-box">
                        <div class="info-box-title">Ubicación de la cita:</div>
                        <div class="info-box-content">
                            @if($direccion)
                            <div><strong>Dirección:</strong> {{ $direccion }}</div>
                            @endif
                            @if($telefono)
                            <div style="margin-top: 8px;"><strong>Teléfono:</strong> {{ $telefono }}</div>
                            @endif
                            @if($email)
                            <div><strong>Email:</strong> {{ $email }}</div>
                            @endif
                        </div>
                    </div>
                    @endif
                @endif
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="clinica-name">{{ $clinicaDisplayName ?? ($clinica->nombre ?? 'Clínica Médica') }}</div>
                <div class="footer-contact">
                    @php
                        $footerTelefono = $sucursal->telefono ?? ($clinica->telefono ?? null);
                        $footerEmail = $sucursal->email ?? ($clinica->email ?? null);
                        $footerDireccion = $sucursal->direccion ?? ($clinica->direccion ?? null);
                    @endphp
                    @if($footerTelefono)<div>Tel: {{ $footerTelefono }}</div>@endif
                    @if($footerEmail)<div>Email: {{ $footerEmail }}</div>@endif
                    @if($footerDireccion)<div>{{ $footerDireccion }}</div>@endif
                </div>
                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #d0d4e0; font-size: 12px; color: #999;">
                    Este es un correo automático, por favor no responder.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
