<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Endodoncia</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; margin: 24px; }
        .header { background: {!! $clinica->color_principal ?? '#0A1628' !!}; color: #fff; padding: 12px 14px; border-radius: 6px; margin-bottom: 14px; }
        .header h1 { font-size: 15px; margin: 0 0 4px; }
        .grid { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .grid td { border: 1px solid #e2e8f0; padding: 6px 8px; vertical-align: top; width: 50%; }
        .label { color: #64748b; font-size: 9px; text-transform: uppercase; letter-spacing: .03em; }
        .val { font-weight: 600; margin-top: 2px; }
        .box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; margin-bottom: 10px; background: #f8fafc; }
        .box h3 { margin: 0 0 6px; font-size: 12px; }
        .foot { font-size: 8px; color: #64748b; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $clinica->nombre ?? 'Clínica' }} — Ficha de Endodoncia</h1>
        <div>{{ $pacienteNombre }} · {{ optional($data->fecha)->format('d/m/Y') }}</div>
    </div>

    <table class="grid">
        <tr>
            <td><div class="label">Pieza</div><div class="val">{{ $data->pieza ?? '—' }}</div></td>
            <td><div class="label">Etapa</div><div class="val">{{ $data->etapa ?? '—' }}</div></td>
        </tr>
        <tr>
            <td><div class="label">Diagnóstico pulpar</div><div class="val">{{ $data->diagnostico_pulpar ?? '—' }}</div></td>
            <td><div class="label">Diagnóstico periapical</div><div class="val">{{ $data->diagnostico_periapical ?? '—' }}</div></td>
        </tr>
        <tr>
            <td><div class="label">Dolor</div><div class="val">{{ $data->dolor ?? '—' }}</div></td>
            <td><div class="label">Conductos</div><div class="val">{{ $data->conductos ?? '—' }}</div></td>
        </tr>
        <tr>
            <td><div class="label">Técnica</div><div class="val">{{ $data->tecnica ?? '—' }}</div></td>
            <td><div class="label">Material de obturación</div><div class="val">{{ $data->material_obturacion ?? '—' }}</div></td>
        </tr>
    </table>

    @php $p = $data->pruebas ?? []; @endphp
    <div class="box">
        <h3>Pruebas clínicas</h3>
        Frío: <strong>{{ $p['frio'] ?? '—' }}</strong> ·
        Calor: <strong>{{ $p['calor'] ?? '—' }}</strong> ·
        Percusión: <strong>{{ $p['percusion'] ?? '—' }}</strong> ·
        Palpación: <strong>{{ $p['palpacion'] ?? '—' }}</strong> ·
        Movilidad: <strong>{{ $p['movilidad'] ?? '—' }}</strong>
    </div>

    @if($data->hallazgos_rx)
        <div class="box"><h3>Hallazgos radiográficos</h3>{{ $data->hallazgos_rx }}</div>
    @endif
    @if($data->tratamiento_realizado)
        <div class="box"><h3>Tratamiento realizado</h3>{{ $data->tratamiento_realizado }}</div>
    @endif
    @if($data->plan_tratamiento)
        <div class="box"><h3>Plan</h3>{{ $data->plan_tratamiento }}</div>
    @endif
    @if($data->observaciones)
        <div class="box"><h3>Observaciones</h3>{{ $data->observaciones }}</div>
    @endif

    <div class="foot">Generado por LynkaMed</div>
</body>
</html>
