<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Ortodoncia</title>
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
        <h1>{{ $clinica->nombre ?? 'Clínica' }} — Ficha de Ortodoncia</h1>
        <div>{{ $pacienteNombre }} · {{ optional($data->fecha)->format('d/m/Y') }}</div>
    </div>

    <table class="grid">
        <tr>
            <td><div class="label">Clase Angle</div><div class="val">{{ $data->clase_angle ?? '—' }}</div></td>
            <td><div class="label">Patrón esqueletal</div><div class="val">{{ $data->patron_esqueletal ?? '—' }}</div></td>
        </tr>
        <tr>
            <td><div class="label">Overjet (mm)</div><div class="val">{{ $data->overjet_mm !== null ? $data->overjet_mm : '—' }}</div></td>
            <td><div class="label">Overbite (mm)</div><div class="val">{{ $data->overbite_mm !== null ? $data->overbite_mm : '—' }}</div></td>
        </tr>
        <tr>
            <td><div class="label">Apiñamiento</div><div class="val">{{ $data->apinamiento ?? '—' }}</div></td>
            <td><div class="label">Hábitos</div><div class="val">{{ $data->habitos ?? '—' }}</div></td>
        </tr>
        <tr>
            <td><div class="label">Tipo de aparato</div><div class="val">{{ $data->tipo_aparato ?? '—' }}</div></td>
            <td><div class="label">Fase</div><div class="val">{{ $data->fase ?? '—' }}</div></td>
        </tr>
        <tr>
            <td colspan="2"><div class="label">Próximo control</div><div class="val">{{ optional($data->proximo_control)->format('d/m/Y') ?? '—' }}</div></td>
        </tr>
    </table>

    @if($data->diagnostico)
        <div class="box"><h3>Diagnóstico</h3>{{ $data->diagnostico }}</div>
    @endif
    @if($data->plan_tratamiento)
        <div class="box"><h3>Plan de tratamiento</h3>{{ $data->plan_tratamiento }}</div>
    @endif
    @if($data->observaciones)
        <div class="box"><h3>Observaciones</h3>{{ $data->observaciones }}</div>
    @endif

    <div class="foot">Generado por LynkaMed</div>
</body>
</html>
