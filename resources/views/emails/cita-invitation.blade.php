<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
            position: relative;
        }
        .header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ffd700 0%, #ff6b6b 50%, #4ecdc4 100%);
        }
        .logo {
            max-width: 160px;
            max-height: 90px;
            margin-bottom: 20px;
            background-color: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .content {
            padding: 40px 35px;
        }
        .greeting {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 15px;
        }
        .intro {
            font-size: 16px;
            color: #555;
            margin-bottom: 30px;
            line-height: 1.7;
        }
        .cita-details {
            background: linear-gradient(135deg, #f8f9fc 0%, #e8eaf0 100%);
            border-left: 5px solid #667eea;
            padding: 25px;
            border-radius: 12px;
            margin: 30px 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .detail-row {
            display: flex;
            align-items: flex-start;
            padding: 12px 0;
            border-bottom: 1px dashed #d0d4e0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 700;
            color: #667eea;
            min-width: 130px;
            font-size: 15px;
        }
        .detail-value {
            color: #333;
            flex: 1;
            font-size: 15px;
            font-weight: 500;
        }
        .alert {
            border-radius: 12px;
            padding: 18px 22px;
            margin: 25px 0;
            font-size: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .alert.success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-left: 5px solid #28a745;
            color: #155724;
        }
        .alert.danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border-left: 5px solid #dc3545;
            color: #721c24;
        }
        .btn-calendar {
            display: inline-block;
            padding: 16px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            transition: transform 0.3s ease;
            text-align: center;
            margin: 30px 0;
        }
        .info-box {
            margin-top: 30px;
            padding: 25px;
            background: linear-gradient(135deg, #e8f4fd 0%, #d1e7f7 100%);
            border-radius: 12px;
            border-left: 5px solid #3b82f6;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .info-box-title {
            color: #1e40af;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .info-box-content {
            line-height: 2;
            color: #334155;
        }
        .recommendations {
            margin-top: 18px;
            padding: 18px;
            background: linear-gradient(135deg, #fef3cd 0%, #fce8a8 100%);
            border-radius: 10px;
            border-left: 4px solid #f59e0b;
        }
        .recommendations-title {
            color: #92400e;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 15px;
        }
        .recommendations ul {
            margin: 12px 0 0 20px;
            color: #78350f;
            font-size: 14px;
            line-height: 1.9;
        }
        .footer {
            background: linear-gradient(135deg, #f8f9fc 0%, #e8eaf0 100%);
            padding: 30px 35px;
            text-align: center;
            color: #666;
            font-size: 14px;
            border-top: 4px solid #667eea;
        }
        .footer .clinica-name {
            font-weight: 700;
            color: #667eea;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .footer-contact {
            margin: 15px 0;
            line-height: 1.9;
        }
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            box-shadow: 0 2px 6px rgba(40, 167, 69, 0.3);
        }
        @media only screen and (max-width: 600px) {
            .content {
                padding: 30px 20px;
            }
            .detail-row {
                flex-direction: column;
            }
            .detail-label {
                min-width: auto;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header con logo -->
            <div class="header">
                <img src="{{ $clinica->logo }}" alt="{{ $clinica->nombre }} Logo" class="logo">
                <h1>{{ $subject }}</h1>
            </div>

            <!-- Contenido -->
            <div class="content">
                <div class="greeting">{{ $greeting }}</div>
                
                <p class="intro">{{ $introLine }}</p>

                @if($action === 'cancel')
                    <div class="alert danger">
                        <strong>⚠️ Atención:</strong> La cita ha sido cancelada.
                        @if($isPatient ?? false)
                            <br>Si necesitas reagendar, por favor contáctanos.
                        @endif
                    </div>
                @else
                    <div class="alert success">
                        <strong>✅ Confirmación:</strong> 
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
                        📅 Ver mi Calendario
                    </a>
                </div>
                @endif

                <!-- Detalles de la cita -->
                <div class="cita-details">
                    <div class="detail-row">
                        <span class="detail-label">👤 Paciente:</span>
                        <span class="detail-value">{{ $paciente->nombre }} {{ $paciente->apellidoPat }} {{ $paciente->apellidoMat }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">📅 Fecha:</span>
                        <span class="detail-value">{{ $fechaFormateada }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">🕐 Hora:</span>
                        <span class="detail-value">{{ $horaFormateada }}</span>
                    </div>
                    @if($cita->primera_vez)
                    <div class="detail-row">
                        <span class="detail-label">🆕 Primera vez:</span>
                        <span class="detail-value"><span class="badge">Primera Consulta</span></span>
                    </div>
                    @endif
                    @if($cita->notas)
                    <div class="detail-row">
                        <span class="detail-label">📝 Notas:</span>
                        <span class="detail-value">{{ $cita->notas }}</span>
                    </div>
                    @endif
                </div>

                @if($isPatient ?? false)
                    <!-- Información para el paciente -->
                    <div class="info-box">
                        <div class="info-box-title">📍 ¿Cómo llegar?</div>
                        <div class="info-box-content">
                            <div><strong>Dirección:</strong> Real de Mayorazgo 130, Local 3</div>
                            <div>Col. Xoco, Benito Juárez, CP 03330, CDMX</div>
                            @if($clinica->direccion ?? null)<div style="color: #64748b; font-size: 13px;">{{ $clinica->direccion }}</div>@endif
                            @if($clinica->telefono ?? null)<div style="margin-top: 12px;"><strong>Teléfono:</strong> 📞 {{ $clinica->telefono }}</div>@endif
                            @if($clinica->email ?? null)<div><strong>Email:</strong> ✉️ {{ $clinica->email }}</div>@endif
                        </div>
                        @if($action !== 'cancel')
                        <div class="recommendations">
                            <div class="recommendations-title">⏰ Recomendaciones importantes:</div>
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
                    @if($paciente->telefono || $paciente->email)
                    <div class="info-box">
                        <div class="info-box-title">📋 Información de contacto del paciente:</div>
                        <div class="info-box-content">
                            @if($paciente->telefono)
                            <div>📞 {{ $paciente->telefono }}</div>
                            @endif
                            @if($paciente->email)
                            <div>✉️ {{ $paciente->email }}</div>
                            @endif
                        </div>
                    </div>
                    @endif
                @endif
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="clinica-name">🏥 {{ $clinica->nombre ?? 'Clínica Médica' }}</div>
                <div class="footer-contact">
                    @if($clinica->telefono ?? null)<div>📞 {{ $clinica->telefono }}</div>@endif
                    @if($clinica->email ?? null)<div>✉️ {{ $clinica->email }}</div>@endif
                    @if($clinica->direccion ?? null)<div>📍 {{ $clinica->direccion }}</div>@endif
                    <div style="font-size: 12px; color: #888; margin-top: 5px;">HSAI Universidad Torre Médica II</div>
                </div>
                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #d0d4e0; font-size: 12px; color: #999;">
                    Este es un correo automático, por favor no responder.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
