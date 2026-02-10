<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Cita - {{ $clinica->nombre }}</title>
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
            width: 48px;
            height: 48px;
            object-fit: contain;
            background: white;
            padding: 8px;
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
        
        .appointment-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            text-align: center;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
            position: relative;
            overflow: hidden;
        }
        
        .appointment-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
            pointer-events: none;
        }
        
        .appointment-card h3 {
            margin: 0 0 25px 0;
            font-size: 26px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .appointment-info {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 25px 0;
            flex-wrap: wrap;
            gap: 25px;
        }
        
        .info-item {
            text-align: center;
            background: rgba(255, 255, 255, 0.2);
            padding: 20px 30px;
            border-radius: 12px;
            min-width: 150px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .info-label {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .info-value {
            font-size: 22px;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .calendar-button {
            display: inline-block;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white !important;
            padding: 16px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .calendar-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.5);
        }
        
        .calendar-note {
            background: linear-gradient(135deg, #fff9e6 0%, #ffecb3 100%);
            border-left: 4px solid #ffa726;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            font-size: 14px;
            color: #e65100;
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
        
        .reminder p {
            margin: 0;
            padding: 0;
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
                padding: 15px 10px;
            }
            
            .email-wrapper {
                border-radius: 12px;
            }
            
            .header {
                padding: 25px 15px;
            }
            
            .header h1 {
                font-size: 20px;
            }
            
            .header p {
                font-size: 14px;
            }
            
            .content {
                padding: 20px 15px;
            }
            
            .content h2 {
                font-size: 18px;
                margin-bottom: 15px;
            }
            
            .content p {
                font-size: 15px;
                margin-bottom: 15px;
            }
            
            .appointment-card {
                padding: 20px 15px;
                margin: 20px 0;
            }
            
            .appointment-card h3 {
                font-size: 18px;
                margin-bottom: 15px;
            }
            
            .appointment-info {
                flex-direction: row;
                gap: 15px;
                margin: 15px 0;
                justify-content: center;
            }
            
            .info-item {
                min-width: 0;
                flex: 1;
                padding: 15px 12px;
                max-width: 48%;
            }
            
            .info-label {
                font-size: 11px;
                margin-bottom: 5px;
            }
            
            .info-value {
                font-size: 16px;
            }
            
            .reminder {
                padding: 15px;
                margin: 20px 0;
            }
            
            .reminder strong {
                font-size: 15px;
                margin-bottom: 10px;
            }
            
            .reminder ul li {
                font-size: 14px;
                padding: 4px 0;
            }
            
            .footer {
                padding: 20px 15px;
            }
            
            .footer p {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <img src="{{ $clinica->logo }}" alt="{{ $clinica->nombre }} Logo">
            <h1>✅ Confirmación de Cita</h1>
            <p>{{ $clinica->nombre }}</p>
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
                        <h3>📅 Cita #{{ $index + 1 }}</h3>
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
                    <strong>📅 Agregar a mi calendario:</strong>
                </p>
                
                <a href="{{ $googleCalendarUrl }}" class="calendar-button" style="display: inline-block; background: linear-gradient(135deg, #4285F4 0%, #0F9D58 100%); color: white !important; padding: 16px 40px; border-radius: 50px; text-decoration: none; font-weight: 700; font-size: 16px; margin: 10px 0; box-shadow: 0 4px 15px rgba(66, 133, 244, 0.4);">
                    + Agregar a Google Calendar
                </a>
                
                <div class="calendar-note" style="margin-top: 20px;">
                    📱 <strong>Alternativa:</strong> Este correo incluye un archivo adjunto (event.ics) que funciona con cualquier calendario (Outlook, Apple Calendar, etc.). 
                    Simplemente descargue el archivo adjunto y ábralo.
                </div>
            </div>
            @else
            <div style="text-align: center; margin: 30px 0;">
                <div class="calendar-note" style="margin-top: 20px;">
                    📱 <strong>Nota sobre el calendario:</strong> Tiene {{ count($citas) }} citas programadas. Por favor, agregue cada una manualmente a su calendario con las fechas y horas mostradas arriba.
                </div>
            </div>
            @endif
            
            @if(isset($cita) && $cita->observaciones)
            <div class="reminder" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border-left: 4px solid #f39c12;">
                <strong style="color: #d68910;">📝 Notas Importantes:</strong>
                <p style="color: #856404; margin: 10px 0 0 0; padding: 0;">{{ $cita->observaciones }}</p>
            </div>
            @endif
            
            <div class="reminder">
                <strong>📌 Recordatorios Importantes:</strong>
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
                <strong>{{ $clinica->nombre ?? 'Clínica Médica' }}</strong>
                @if($clinica && $clinica->telefono)
                    Tel: {{ $clinica->telefono }}<br>
                @endif
                @if($clinica && $clinica->email)
                    Email: {{ $clinica->email }}<br>
                @endif
                @if($clinica && $clinica->direccion)
                    {{ $clinica->direccion }}
                @endif
            </p>
        </div>
    </div>
</body>
</html>
