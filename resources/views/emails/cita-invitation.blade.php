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
        .email-wrapper { max-width: 600px; margin: 0 auto; }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }
        .header {
            background: {!! $clinica->color_principal ?? '#0A1628' !!};
            padding: 28px 30px 24px;
            text-align: center;
            color: #ffffff;
        }
        .clinic-logo {
            max-width: 160px;
            max-height: 70px;
            object-fit: contain;
            margin-bottom: 14px;
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
            line-height: 1.25;
        }
        .header-meta {
            margin-top: 10px;
            font-size: 14px;
            color: #cbd5e1;
            line-height: 1.45;
        }
        .content { padding: 32px 35px; }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: {!! $clinica->color_principal ?? '#0A1628' !!};
            margin-bottom: 12px;
        }
        .intro {
            font-size: 15px;
            color: #475569;
            margin-bottom: 22px;
            line-height: 1.7;
        }
        .cita-details {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid {!! $clinica->color_principal ?? '#0A1628' !!};
            padding: 22px 24px;
            border-radius: 8px;
            margin: 22px 0;
        }
        .detail-row {
            display: flex;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .detail-row:last-child { border-bottom: none; padding-bottom: 0; }
        .detail-label {
            font-weight: 700;
            color: #64748b;
            min-width: 132px;
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
            margin: 18px 0;
            font-size: 14px;
            line-height: 1.55;
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
        .alert.neutral {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #475569;
            color: #334155;
        }
        .btn-calendar {
            display: inline-block;
            padding: 13px 28px;
            background: #1d4ed8;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 700;
            font-size: 15px;
            text-align: center;
            margin: 8px 0 0;
        }
        .btn-row { text-align: center; margin: 8px 0 4px; }
        .info-box {
            margin-top: 22px;
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
            margin: 8px 0 0 18px;
            color: #78350f;
            font-size: 13px;
            line-height: 1.85;
        }
        .footer {
            background: #f8fafc;
            padding: 24px 30px;
            text-align: center;
            color: #64748b;
            font-size: 13px;
            border-top: 2px solid {!! $clinica->color_principal ?? '#0A1628' !!};
        }
        .footer .clinica-name {
            font-weight: 700;
            color: {!! $clinica->color_principal ?? '#0A1628' !!};
            font-size: 16px;
            margin-bottom: 12px;
        }
        .footer-contact { margin: 12px 0; line-height: 1.9; }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            background: #0d9488;
            color: white;
        }
        .badge-muted {
            background: #64748b;
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
@php
    $direccionSuc = optional($sucursal)->direccion ?? optional($clinica)->direccion ?? null;
    $telefonoSuc = optional($sucursal)->telefono ?? optional($clinica)->telefono ?? null;
    $emailSuc = optional($sucursal)->email ?? optional($clinica)->email ?? null;

    $clinicaLogoImgSrc = null;
    $logoPath = isset($clinicaLogoModel) && $clinicaLogoModel
        ? \App\Helpers\EmailHelper::clinicaLogoPath($clinicaLogoModel)
        : null;
    if ($logoPath && isset($message) && $message) {
        try {
            $clinicaLogoImgSrc = $message->embed($logoPath);
        } catch (\Throwable $e) {
            $clinicaLogoImgSrc = null;
        }
    }
    if (! $clinicaLogoImgSrc && isset($clinicaLogoModel) && $clinicaLogoModel && $clinicaLogoModel->logo) {
        $clinicaLogoImgSrc = rtrim(config('app.url'), '/').'/storage/'.ltrim($clinicaLogoModel->logo, '/');
    }
@endphp
    <div class="email-wrapper">
        <div class="email-container">
            <div class="header">
                @if(!empty($clinicaLogoImgSrc))
                    <img src="{{ $clinicaLogoImgSrc }}" alt="{{ $clinicaDisplayName ?? optional($clinica)->nombre ?? 'Clínica' }} Logo" class="clinic-logo">
                @endif
                <h1>{{ $headerTitle ?? 'Cita' }}</h1>
                <p class="header-meta">{{ $headerSubtitle ?? $clinicaDisplayName }}</p>
            </div>

            <div class="content">
                <div class="greeting">{{ $greeting }}</div>
                <p class="intro">{{ $introLine }}</p>

                @if($action === 'cancel')
                    <div class="alert danger">
                        <strong>Importante:</strong>
                        @if($isPatient ?? false)
                            Esta cita ya no tiene vigencia. Si necesitas reagendar, contáctanos por los datos de la clínica al final del correo.
                        @else
                            Actualiza tu agenda personal si habías importado el evento desde el archivo adjunto.
                        @endif
                    </div>
                @else
                    <div class="alert success">
                        <strong>Confirmación:</strong>
                        @if($isPatient ?? false)
                            Tu cita quedó registrada en {{ $clinicaDisplayName }}.
                        @else
                            La cita quedó guardada en Lynkamed con el estado indicado abajo.
                        @endif
                    </div>
                @endif

                @if($isPatient ?? false)
                    <div class="alert neutral">
                        <strong>Calendario:</strong> Este mensaje incluye un archivo adjunto <strong>cita.ics</strong>. Ábrelo para añadir el evento a Google Calendar, Outlook o Apple Calendar.
                    </div>
                    @if($action !== 'cancel')
                    <div class="btn-row">
                        <a href="{{ $calendarUrl }}" class="btn-calendar">Abrir calendario en Lynkamed</a>
                    </div>
                    @endif
                @else
                    @if($action !== 'cancel')
                    <div class="btn-row">
                        <a href="{{ $calendarUrl }}" class="btn-calendar">Ir al calendario en Lynkamed</a>
                    </div>
                    @endif
                    <div class="alert neutral">
                        <strong>Archivo adjunto:</strong> <strong>cita.ics</strong> — impórtalo en tu aplicación de calendario para no perder la fecha.
                    </div>
                @endif

                <div class="cita-details">
                    <div class="detail-row">
                        <span class="detail-label">Paciente</span>
                        <span class="detail-value">{{ $paciente->nombre }} {{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Fecha</span>
                        <span class="detail-value">{{ $fechaFormateada }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Hora</span>
                        <span class="detail-value">{{ $horaFormateada }}</span>
                    </div>
                    @if(!($isPatient ?? false))
                    <div class="detail-row">
                        <span class="detail-label">Estado</span>
                        <span class="detail-value">{{ $estadoLabel ?? '—' }}</span>
                    </div>
                    @endif
                    @if(!($isPatient ?? false) && !empty($doctorAsignadoNombre))
                    <div class="detail-row">
                        <span class="detail-label">Profesional</span>
                        <span class="detail-value">{{ $doctorAsignadoNombre }}</span>
                    </div>
                    @endif
                    @if(!($isPatient ?? false) && !empty($adminNombre))
                    <div class="detail-row">
                        <span class="detail-label">Agendó</span>
                        <span class="detail-value">{{ $adminNombre }}</span>
                    </div>
                    @endif
                    @if(!($isPatient ?? false) && $cita->primera_vez)
                    <div class="detail-row">
                        <span class="detail-label">Consulta</span>
                        <span class="detail-value"><span class="badge badge-muted">Primera vez</span></span>
                    </div>
                    @endif
                    @if($cita->primera_vez && ($isPatient ?? false))
                    <div class="detail-row">
                        <span class="detail-label">Tipo</span>
                        <span class="detail-value"><span class="badge">Primera consulta</span></span>
                    </div>
                    @endif
                    @if($cita->notas)
                    <div class="detail-row">
                        <span class="detail-label">Notas</span>
                        <span class="detail-value">{{ $cita->notas }}</span>
                    </div>
                    @endif
                </div>

                @if($isPatient ?? false)
                    <div class="info-box">
                        <div class="info-box-title">Ubicación</div>
                        <div class="info-box-content">
                            @if($direccionSuc)
                                <div><strong>Dirección:</strong> {{ $direccionSuc }}</div>
                            @endif
                            @if($telefonoSuc)
                                <div style="margin-top: 10px;"><strong>Teléfono:</strong> {{ $telefonoSuc }}</div>
                            @endif
                            @if($emailSuc)
                                <div><strong>Correo:</strong> {{ $emailSuc }}</div>
                            @endif
                        </div>
                        @if($action !== 'cancel')
                        <div class="recommendations">
                            <div class="recommendations-title">Recomendaciones</div>
                            <ul>
                                <li>Llega 10 minutos antes de tu cita</li>
                                <li>Trae identificación oficial</li>
                                @if($cita->primera_vez)
                                <li>Si es tu primera vez, lleva estudios previos si los tienes</li>
                                @endif
                                <li>Si no puedes asistir, avisa con la mayor anticipación posible</li>
                            </ul>
                        </div>
                        @endif
                    </div>
                @else
                    @if($paciente->telefono || $paciente->email || $paciente->domicilio)
                    <div class="info-box">
                        <div class="info-box-title">Contacto del paciente</div>
                        <div class="info-box-content">
                            @if($paciente->telefono)
                            <div><strong>Tel:</strong> {{ $paciente->telefono }}</div>
                            @endif
                            @if($paciente->email)
                            <div><strong>Correo:</strong> {{ $paciente->email }}</div>
                            @endif
                            @if($paciente->domicilio)
                            <div style="margin-top: 8px;">{{ $paciente->domicilio }}</div>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($direccionSuc || $telefonoSuc || $emailSuc)
                    <div class="info-box">
                        <div class="info-box-title">Ubicación de la cita</div>
                        <div class="info-box-content">
                            @if($direccionSuc)
                            <div><strong>Dirección:</strong> {{ $direccionSuc }}</div>
                            @endif
                            @if($telefonoSuc)
                            <div style="margin-top: 8px;"><strong>Teléfono:</strong> {{ $telefonoSuc }}</div>
                            @endif
                            @if($emailSuc)
                            <div><strong>Correo recepción:</strong> {{ $emailSuc }}</div>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($action !== 'cancel')
                    <div class="recommendations">
                        <div class="recommendations-title">Recordatorios para tu agenda</div>
                        <ul>
                            <li>Revisa en Lynkamed el expediente del paciente antes de la consulta</li>
                            <li>Si la cita sigue en <strong>pendiente</strong>, confirma asistencia con el paciente</li>
                            <li>Los cambios futuros en fecha u hora generarán un nuevo correo con .ics actualizado</li>
                        </ul>
                    </div>
                    @endif
                @endif
            </div>

            <div class="footer">
                <div class="clinica-name">{{ $clinicaDisplayName ?? optional($clinica)->nombre ?? 'Clínica Médica' }}</div>
                <div class="footer-contact">
                    @if($telefonoSuc)<div>Tel: {{ $telefonoSuc }}</div>@endif
                    @if($emailSuc)<div>{{ $emailSuc }}</div>@endif
                    @if($direccionSuc)<div>{{ $direccionSuc }}</div>@endif
                </div>
                <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8;">
                    Correo automático. Para dudas, contacta directamente a la clínica.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
