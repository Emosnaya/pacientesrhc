<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Nota de Seguimiento Pulmonar</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #1e293b;
            background: #ffffff;
            padding: 10px 20px;
        }
        .logo-container { height: 36px; overflow: hidden; display: inline-block; }
        .logo-container img { height: 36px; width: auto; }
        .paciente { font-size: 10px; }
        .f-bold { font-weight: bold; }
        .f-normal { font-weight: normal; }
        .f-10 { font-size: 8.5px; }
        .f-15 { font-size: 13px; }
        .text-center { text-align: center; }
        .text-lft { text-align: left; }
        .medio { position: relative; }
        .texto-izquierda { text-align: left; position: absolute; left: 0; }
        .texto-derecha { text-align: right; position: absolute; right: 0; }
        .section-label { font-size: 8px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; padding-bottom: 3px; border-bottom: 2px solid #0A1628; margin-bottom: 5px; margin-top: 8px; }
        .info-block { background: #f8fafc; border: 1px solid #e2e8f0; border-left: 3px solid #0A1628; padding: 6px 10px; margin-bottom: 8px; font-size: 10px; color: #334155; }
        .soap-section { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .soap-letter { width: 28px; color: white; font-weight: 700; font-size: 14px; text-align: center; padding: 8px 4px; vertical-align: top; }
        .soap-body { padding: 6px 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-left: none; vertical-align: top; }
        .soap-sublabel { font-size: 8px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .soap-text { font-size: 10px; color: #334155; }
        .signature { margin-top: 3rem; text-align: center; width: 100%; }
        .signature img { display: block; margin: 0 auto 0.2rem; max-width: 150px; height: auto; }
        .signature-line { border-top: 1px solid #000; width: 250px; margin: 0.2rem auto 0.3rem; }
        .signature-text { font-size: 8px; text-align: center; margin: 0.2rem 0; }
        /* === HEADER MODERNO === */
        .header { width: 100%; background: #0A1628; border-radius: 8px; margin-bottom: 10px; padding: 8px 12px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; padding: 0; }
        .header-logo-cell { width: 60px; padding-right: 12px !important; }
        .header-logo { width: 45px; height: 45px; background: white; border-radius: 6px; padding: 5px; text-align: center; }
        .header-logo img { max-height: 35px; max-width: 35px; }
        .header-title { font-size: 16px; font-weight: 700; color: white; letter-spacing: -0.5px; }
        .header-subtitle { font-size: 9px; color: #94a3b8; }
        .header-meta-cell { text-align: right; width: 120px; }
        .header-badge { background: rgba(255,255,255,0.15); padding: 5px 10px; border-radius: 5px; display: inline-block; margin-bottom: 4px; }
        .header-badge-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; }
        .header-badge-value { font-size: 12px; font-weight: 700; color: white; }
        .header-date { font-size: 9px; color: #94a3b8; }
        .patient-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; margin-bottom: 10px; }
        .patient-table { width: 100%; border-collapse: collapse; }
        .patient-table td { padding: 2px 6px; font-size: 10px; }
        .patient-name { font-size: 13px; font-weight: 700; color: #0A1628; margin-bottom: 6px; }
        .patient-label { color: #64748b; font-size: 9px; }
        .patient-value { font-weight: 600; color: #334155; }
        .patient-diagnosis { margin-top: 6px; padding-top: 6px; border-top: 1px solid #e2e8f0; font-size: 10px; }
        .patient-diagnosis-label { font-size: 9px; color: #64748b; font-weight: 600; }
        .page-footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 6px 20px; background: white; border-top: 2px solid #0A1628; font-size: 9px; }
        .page-footer-table { width: 100%; }
        .page-footer .clinic-name { font-weight: 700; color: #ef4444; }
        .page-footer .clinic-contact { text-align: right; color: #64748b; }
        .content-wrapper { padding-bottom: 35px; }
    </style>
</head>
<body>
    <!-- PAGE FOOTER (fixed) -->
    <div class="page-footer">
        <table class="page-footer-table">
            <tr>
                <td class="clinic-name">{{ $clinica->nombre ?? '' }}</td>
                <td class="clinic-contact">
                    {{ $clinica->telefono ?? '' }}
                    @if($clinica->email ?? null)
                        | {{ $clinica->email }}
                    @endif
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; padding-top: 4px; font-size: 7px; color: #94a3b8;">
                    <span>Generado con</span> <strong style="color: #0A1628;">Lynkamed</strong>
                </td>
            </tr>
        </table>
    </div>
    <div class="content-wrapper">
    <!-- HEADER -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-logo-cell">
                    <div class="header-logo">
                        @if(isset($clinicaLogo) && $clinicaLogo)
                            <img src="{{ $clinicaLogo }}" alt="Logo">
                        @endif
                    </div>
                </td>
                <td style="padding-left: 10px;">
                    <div class="header-title">Nota de Seguimiento Pulmonar</div>
                    <div class="header-subtitle">Rehabilitación Pulmonar</div>
                </td>
                <td class="header-meta-cell">
                    <div class="header-badge">
                        <div class="header-badge-label">Registro</div>
                        <div class="header-badge-value">#{{ $paciente->registro ?? '' }}</div>
                    </div>
                    <div class="header-date">{{ $data->fecha_consulta ? $data->fecha_consulta->format('d/m/Y') : '' }}</div>
                </td>
            </tr>
        </table>
    </div>
    <!-- PATIENT INFO -->
    <div class="patient-card">
        <div class="patient-name">{{ $paciente->apellidoPat ?? '' }} {{ $paciente->apellidoMat ?? '' }} {{ $paciente->nombre ?? '' }}</div>
        <table class="patient-table">
            <tr>
                <td><span class="patient-label">Edad:</span> <span class="patient-value">{{ $paciente->edad ?? '' }} años</span></td>
                <td><span class="patient-label">Hora:</span> <span class="patient-value">{{ $data->hora_consulta ? \Carbon\Carbon::parse($data->hora_consulta)->format('H:i') : '' }}</span></td>
            </tr>
        </table>
    </div>

    <main class="mt-0">
        @if($data->ficha_identificacion)
        <div class="section-label">Ficha de identificación</div>
        <div class="info-block">{{ $data->ficha_identificacion }}</div>
        @endif

        @if($data->diagnosticos)
        <div class="section-label">Diagnósticos</div>
        <div class="info-block">{{ $data->diagnosticos }}</div>
        @endif

        <table class="soap-section">
            <tr>
                <td class="soap-letter" style="background:#0A1628;">S</td>
                <td class="soap-body">
                    <div class="soap-sublabel">Subjetivo</div>
                    <div class="soap-text">{{ $data->s_subjetivo ?: '—' }}</div>
                </td>
            </tr>
        </table>
        <table class="soap-section">
            <tr>
                <td class="soap-letter" style="background:#1d4ed8;">O</td>
                <td class="soap-body">
                    <div class="soap-sublabel">Objetivo</div>
                    <div class="soap-text">{{ $data->o_objetivo ?: '—' }}</div>
                </td>
            </tr>
        </table>
        <table class="soap-section">
            <tr>
                <td class="soap-letter" style="background:#0d9488;">A</td>
                <td class="soap-body">
                    <div class="soap-sublabel">Apreciación</div>
                    <div class="soap-text">{{ $data->a_apreciacion ?: '—' }}</div>
                </td>
            </tr>
        </table>
        <table class="soap-section">
            <tr>
                <td class="soap-letter" style="background:#6d28d9;">P</td>
                <td class="soap-body">
                    <div class="soap-sublabel">Plan</div>
                    <div class="soap-text">{{ $data->p_plan ?: '—' }}</div>
                </td>
            </tr>
        </table>

        @if(isset($firmaBase64) && $firmaBase64)
        <div class="signature">
            <img src="{{ $firmaBase64 }}" alt="Firma">
            <div class="signature-line"></div>
            <p class="signature-text f-bold mb-0">{{ $user->nombre_con_titulo }}</p>
            @if($user->cedula ?? null)
            <p class="signature-text mb-0">Cédula: {{ $user->cedula }}</p>
            @endif
        </div>
        @endif
    </main>
    </div><!-- End content-wrapper -->
</body>
</html>
