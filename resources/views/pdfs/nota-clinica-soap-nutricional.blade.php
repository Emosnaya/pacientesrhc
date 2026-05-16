<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota Clínica SOAP Nutricional</title>
    <style>
        @font-face { font-family: 'DejaVu Sans'; src: url('{{ storage_path('fonts/DejaVuSans.ttf') }}'); }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111827; margin: 20px 24px; }
        .header { background: {!! $clinica->color_principal ?? '#0A1628' !!}; color: white; border-radius: 8px; padding: 10px 12px; margin-bottom: 10px; }
        .header table, .card table, .soap, .meta { width: 100%; border-collapse: collapse; }
        .logo { background: white; border-radius: 6px; padding: 4px; width: 48px; height: 48px; text-align: center; }
        .logo img { max-height: 38px; max-width: 38px; }
        .card { border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 8px; padding: 8px 10px; margin-bottom: 10px; }
        .section { border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; margin-bottom: 10px; }
        .section-title { background: {!! $clinica->color_principal ?? '#0A1628' !!}; color: white; font-size: 10px; font-weight: 700; padding: 5px 8px; }
        .soap td, .soap th { border: 1px solid #e2e8f0; vertical-align: top; padding: 6px; }
        .soap .letter { width: 34px; font-size: 34px; font-weight: 700; text-align: center; color: #111827; }
        .lbl { color: #64748b; font-size: 8px; }
        .val { font-weight: 700; color: #334155; }
        .box { min-height: 70px; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 4px 14px; border-top: 2px solid {!! $clinica->color_principal ?? '#0A1628' !!}; font-size: 8px; background: #fff; }
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer-name { font-weight: 700; color: {!! $clinica->color_principal ?? '#0A1628' !!}; }
        .footer-contact { text-align: right; color: #64748b; }
        .content-wrap { padding-bottom: 42px; }
        .firma-wrap { margin-top: 18px; text-align: center; }
        .line { border-top: 1px solid #334155; margin: 4px auto 0; width: 180px; }
        .firma-meta { font-size: 8px; color: #64748b; }
    </style>
</head>
<body>
@php
$s = $nota->subjetivo ?? [];
$o = $nota->objetivo ?? [];
$a = $nota->analisis ?? [];
$p = $nota->plan ?? [];
$profesionalNombre = trim($nota->nutriologo_evaluador ?: (($user->nombre_con_titulo ?? '') ?: (($user->nombre ?? '') . ' ' . ($user->apellidoPat ?? '') . ' ' . ($user->apellidoMat ?? ''))));
$profesionalRol = $user?->rol ? config('roles.lista.' . $user->rol, 'Profesional responsable') : 'Profesional responsable';
@endphp
<div class="footer">
    <table class="footer-table">
        <tr>
            <td class="footer-name">{{ $clinica->nombre ?? 'Clínica' }}</td>
            <td class="footer-contact">{{ $clinica->telefono ?? '' }} @if($clinica->email ?? null) | {{ $clinica->email }} @endif</td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:center;padding-top:3px;color:#94a3b8;">Generado con <strong style="color:{!! $clinica->color_principal ?? '#0A1628' !!};">Lynkamed</strong></td>
        </tr>
    </table>
</div>
<div class="content-wrap">
<div class="header">
    <table><tr>
        @if($clinicaLogo)<td style="width:60px"><div class="logo"><img src="{{ $clinicaLogo }}" alt="Logo"></div></td>@endif
        <td><div style="font-size:16px;font-weight:700;">Nota clínica nutricional</div><div style="font-size:12px;font-weight:700;color:#94a3b8;">Formato SOAP</div></td>
        <td style="text-align:right">Fecha: {{ $nota->fecha_elaboracion?->format('d/m/Y') }}<br>No. seguimiento: {{ $nota->numero_seguimiento ?? 'S/N' }}</td>
    </tr></table>
</div>
<div class="card">
    <table><tr>
        <td><div class="lbl">Paciente</div><div class="val">{{ $paciente->nombre ?? '' }} {{ $paciente->apellidoPat ?? '' }} {{ $paciente->apellidoMat ?? '' }}</div></td>
        <td><div class="lbl">Registro</div><div class="val">{{ $paciente->registro ?? 'N/A' }}</div></td>
        <td><div class="lbl">Profesional responsable</div><div class="val">{{ $profesionalNombre ?: 'N/A' }}</div></td>
        <td><div class="lbl">Encargado en turno</div><div class="val">{{ $nota->encargado_turno ?? '—' }}</div></td>
    </tr></table>
</div>
<div class="section">
    <div class="section-title">Formato SOAP</div>
    <table class="soap">
        <tr>
            <td class="letter">S</td>
            <td class="box">
                <strong>Generalidades del paciente:</strong><br>{{ $s['generalidades_paciente'] ?? '—' }}<br><br>
                <strong>Síntomas:</strong><br>{{ $s['sintomas'] ?? '—' }}<br><br>
                <strong>Hábitos y generalidades de estilo de vida:</strong><br>{{ $s['habitos_estilo_vida'] ?? '—' }}
            </td>
        </tr>
        <tr>
            <td class="letter">O</td>
            <td class="box">
                <strong>Diagnóstico médico:</strong><br>{{ $o['diagnostico_medico'] ?? '—' }}<br><br>
                <strong>Tratamiento médico:</strong><br>{{ $o['tratamiento_medico'] ?? '—' }}<br><br>
                <strong>A:</strong> {{ $o['a'] ?? '—' }}<br><br>
                <strong>B:</strong> {{ $o['b'] ?? '—' }}<br><br>
                <strong>C:</strong> {{ $o['c'] ?? '—' }}<br><br>
                <strong>D:</strong> {{ $o['d'] ?? '—' }}
            </td>
        </tr>
        <tr>
            <td class="letter">A</td>
            <td class="box">
                <strong>Diagnósticos nutricionales:</strong><br>{{ $a['diagnosticos_nutricionales'] ?? '—' }}<br><br>
                <strong>Requerimiento de energía y % adecuación:</strong><br>{{ $a['requerimiento_energia'] ?? '—' }}<br>
                <strong>Requerimiento de proteína y % adecuación:</strong><br>{{ $a['requerimiento_proteina'] ?? '—' }}<br>
                <strong>Requerimiento de lípidos y % adecuación:</strong><br>{{ $a['requerimiento_lipidos'] ?? '—' }}<br>
                <strong>Requerimiento de HCO y % adecuación:</strong><br>{{ $a['requerimiento_hco'] ?? '—' }}<br>
                <strong>Requerimiento de fibra y % adecuación:</strong><br>{{ $a['requerimiento_fibra'] ?? '—' }}<br>
                <strong>Requerimiento hídrico y % adecuación:</strong><br>{{ $a['requerimiento_hidrico'] ?? '—' }}<br><br>
                <strong>Objetivos del tratamiento nutricional:</strong><br>{{ $a['objetivos_tratamiento'] ?? '—' }}
            </td>
        </tr>
        <tr>
            <td class="letter">P</td>
            <td class="box">
                <strong>Descripción del plan de alimentación:</strong><br>{{ $p['descripcion_plan_alimentacion'] ?? '—' }}
            </td>
        </tr>
    </table>
</div>
<div class="firma-wrap">
    @if($firmaBase64)<img src="{{ $firmaBase64 }}" alt="Firma" style="max-height:55px;">@endif
    <div class="line"></div>
    <div><strong>{{ $profesionalNombre ?: 'Profesional responsable' }}</strong></div>
    @if($user->cedula_especialista ?? null)<div class="firma-meta">Cédula: {{ $user->cedula_especialista }}</div>@endif
    <div class="firma-meta">{{ $profesionalRol }}</div>
</div>
</div>
</body>
</html>
