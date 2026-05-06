<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Cita - {{ $clinicaDisplayName ?? ($clinica ? $clinica->nombre : 'Clínica Médica') }}</title>
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
            background: {!! $clinica->color_principal ?? '#0A1628' !!};
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
            color: {!! $clinica->color_principal ?? '#0A1628' !!};
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
        .appointment-card {
            background: {!! $clinica->color_principal ?? '#0A1628' !!};
            color: white;
            border-radius: 8px;
            padding: 24px;
            margin: 24px 0;
            text-align: center;
        }
        .appointment-card h3 {
            margin: 0 0 20px 0;
            font-size: 18px;
            font-weight: 700;
            color: white;
        }
        .appointment-info {
            display: flex;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .info-item {
            text-align: center;
            background: rgba(255,255,255,0.12);
            padding: 16px 24px;
            border-radius: 6px;
            min-width: 130px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .info-label {
            font-size: 11px;
            color: #94a3b8;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .info-value {
            font-size: 20px;
            font-weight: 700;
            color: white;
        }
        .calendar-button {
            display: inline-block;
            background: #1d4ed8;
            color: white !important;
            padding: 13px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            margin: 10px 0;
        }
        .calendar-note {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid #f59e0b;
            border-radius: 6px;
            padding: 14px 16px;
            margin: 14px 0;
            font-size: 14px;
            color: #92400e;
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
        .reminder p {
            margin: 0;
            padding: 0;
        }
        .footer {
            background: #f8fafc;
            border-top: 2px solid {!! $clinica->color_principal ?? '#0A1628' !!};
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
            color: {!! $clinica->color_principal ?? '#0A1628' !!};
            font-size: 14px;
            display: block;
            margin-bottom: 6px;
        }
        @media only screen and (max-width: 600px) {
            body { padding: 16px 10px; }
            .header { padding: 24px 20px; }
            .header h1 { font-size: 18px; }
            .content { padding: 20px 16px; }
            .content h2 { font-size: 17px; }
            .appointment-card { padding: 18px 14px; }
            .appointment-card h3 { font-size: 16px; }
            .appointment-info { gap: 10px; }
            .info-item { min-width: 0; flex: 1; padding: 12px; }
            .info-value { font-size: 16px; }
            .footer { padding: 18px 16px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            @if($clinica && $clinica->logo)
                <img src="{{ $clinica->logo_url ?? config('app.url') . '/storage/' . $clinica->logo }}" alt="{{ $clinicaDisplayName ?? $clinica->nombre }} Logo">
            @endif
            <h1>Confirmación de Cita</h1>
            <p>{{ $clinicaDisplayName ?? ($clinica ? $clinica->nombre : 'Clínica Médica') }}</p>
        </div>
        
        <div class="content">
            @if(isset($citas) && count($citas) > 1)
                <h2>¡Sus {{ count($citas) }} citas han sido confirmadas!</h2>
            @else
                <h2>¡Su cita ha sido confirmada!</h2>
            @endif
            
            <p>Estimado/a <strong>{{ $paciente->nombre }} {{ $paciente->apellidoPat }}</strong>,</p>
            
            @if(isset($citas) && count($citas) > 1)
                <p>Nos complace confirmar que sus <strong>{{ count($citas) }} citas</strong> han sido programadas exitosamente. A continuación encontrará todos los detalles:</p>
                
                @foreach($citas as $index => $citaItem)
                    <div class="appointment-card" style="margin-top: {{ $index > 0 ? '20px' : '30px' }};">
                        <h3>Cita #{{ $index + 1 }}</h3>
                        <div class="appointment-info">
                            <div class="info-item">
                                <div class="info-label">Fecha</div>
                                <div class="info-value">{{ \Carbon\Carbon::parse($citaItem->fecha)->format('d/m/Y') }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Hora</div>
                                <div class="info-value">{{ \Carbon\Carbon::parse($citaItem->hora)->format('H:i') }}</div>
                            </div>
                        </div>
                        @if($citaItem->notas)
                            <div style="margin-top: 15px; padding: 12px; background: rgba(255,255,255,0.2); border-radius: 8px;">
                                <div style="font-size: 12px; opacity: 0.9; margin-bottom: 5px;">NOTAS:</div>
                                <div style="font-size: 14px;">{{ $citaItem->notas }}</div>
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <p>Nos complace confirmar que su cita ha sido programada exitosamente. A continuación encontrará todos los detalles:</p>
                
                <div class="appointment-card">
                    <h3>Su Cita Médica</h3>
                    <div class="appointment-info">
                        <div class="info-item">
                            <div class="info-label">Fecha</div>
                            <div class="info-value">{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Hora</div>
                            <div class="info-value">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</div>
                        </div>
                    </div>
                </div>
            @endif
            
            @php
                // Generar URL de Google Calendar - extraer solo fecha y solo hora
                // Para cita única o la primera cita si hay múltiples
                $citaParaCalendario = isset($citas) && count($citas) > 0 ? $citas[0] : $cita;
                $fechaSolo = \Carbon\Carbon::parse($citaParaCalendario->fecha)->format('Y-m-d');
                $horaSolo = \Carbon\Carbon::parse($citaParaCalendario->hora)->format('H:i:s');
                $fechaInicio = \Carbon\Carbon::parse("{$fechaSolo} {$horaSolo}");
                $fechaFin = $fechaInicio->copy()->addHour();
                
                $googleCalendarUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE';
                $clinicaNombre = $clinica->nombre ?? 'Clínica Médica';
                
                if(isset($citas) && count($citas) > 1) {
                    $googleCalendarUrl .= '&text=' . urlencode('Primera Cita Médica - ' . $clinicaNombre);
                    $detalles = 'Primera de ' . count($citas) . ' citas programadas en ' . $clinicaNombre;
                } else {
                    $googleCalendarUrl .= '&text=' . urlencode('Cita Médica - ' . $clinicaNombre);
                    $detalles = 'Cita en ' . $clinicaNombre;
                }
                
                $googleCalendarUrl .= '&dates=' . $fechaInicio->format('Ymd\THis\Z') . '/' . $fechaFin->format('Ymd\THis\Z');
                $googleCalendarUrl .= '&details=' . urlencode($detalles);
                if ($clinica && $clinica->direccion) {
                    $googleCalendarUrl .= '&location=' . urlencode($clinica->direccion);
                }
                $googleCalendarUrl .= '&sf=true&output=xml';
            @endphp
            
            @if(!isset($citas) || count($citas) <= 1)
            <div style="text-align: center; margin: 30px 0;">
                <p style="margin-bottom: 15px; font-size: 16px; color: #2c3e50;">
                    <strong>Agregar a mi calendario:</strong>
                </p>
                
                <a href="{{ $googleCalendarUrl }}" class="calendar-button" style="display: inline-block; background: #1d4ed8; color: white !important; padding: 13px 30px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 15px; margin: 10px 0;">
                    + Agregar a Google Calendar
                </a>
                
                <div class="calendar-note" style="margin-top: 20px;">
                    <strong>Nota:</strong> Este correo incluye un archivo adjunto (event.ics) que funciona con cualquier calendario (Outlook, Apple Calendar, etc.). 
                    Simplemente descargue el archivo adjunto y ábralo.
                </div>
            </div>
            @else
            <div style="text-align: center; margin: 30px 0;">
                <div class="calendar-note" style="margin-top: 20px;">
                    <strong>Nota sobre el calendario:</strong> Tiene {{ count($citas) }} citas programadas. Por favor, agregue cada una manualmente a su calendario con las fechas y horas mostradas arriba.
                </div>
            </div>
            @endif
            
            @if(isset($cita) && $cita->observaciones)
            <div class="reminder" style="background: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #f59e0b;">
                <strong style="color: #92400e;">Notas Importantes:</strong>
                <p style="color: #856404; margin: 10px 0 0 0; padding: 0;">{{ $cita->observaciones }}</p>
            </div>
            @endif
            
            <div class="reminder">
                <strong>Recordatorios Importantes:</strong>
                <ul>
                    <li>Por favor llegue 15 minutos antes de su cita</li>
                    <li>Traiga una identificación oficial (INE, pasaporte, etc.)</li>
                    <li>Si es su primera consulta, traiga estudios médicos previos si los tiene</li>
                    <li>Si necesita reprogramar o cancelar, contacte con al menos 24 horas de anticipación</li>
                </ul>
            </div>
            
            <p>Esperamos verlo pronto. Si tiene alguna pregunta, no dude en contactarnos.</p>
            
            <p><strong>¡Gracias por confiar en nosotros para su cuidado médico!</strong></p>
        </div>
        
        <div class="footer">
            <p>
                <strong>{{ $clinicaDisplayName ?? ($clinica->nombre ?? 'Clínica Médica') }}</strong>
                @php
                    $footerTelefono = $sucursal->telefono ?? ($clinica->telefono ?? null);
                    $footerEmail = $sucursal->email ?? ($clinica->email ?? null);
                    $footerDireccion = $sucursal->direccion ?? ($clinica->direccion ?? null);
                @endphp
                @if($footerTelefono)
                    Tel: {{ $footerTelefono }}<br>
                @endif
                @if($footerEmail)
                    Email: {{ $footerEmail }}<br>
                @endif
                @if($footerDireccion)
                    {{ $footerDireccion }}
                @endif
            </p>
        </div>
    </div>
</body>
</html>
