<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Periodontograma</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #1e293b; margin: 16px; }
        .header { background: {!! $clinica->color_principal ?? '#0A1628' !!}; color: #fff; padding: 10px 12px; border-radius: 6px; margin-bottom: 10px; }
        .header h1 { font-size: 13px; margin: 0; }
        .meta { margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #cbd5e1; padding: 2px 3px; text-align: center; }
        th { background: #f1f5f9; font-size: 7px; }
        .red { background: #fee2e2; color: #991b1b; font-weight: bold; }
        .sum { background: #f8fafc; border: 1px solid #e2e8f0; padding: 6px; border-radius: 4px; margin-bottom: 8px; }
        .foot { font-size: 7px; color: #64748b; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $clinica->nombre ?? 'Clínica' }} — Periodontograma</h1>
        <div>{{ $pacienteNombre }} · {{ optional($data->fecha)->format('d/m/Y') }}</div>
    </div>

    <div class="sum">
        % BOP: <strong>{{ $data->porcentaje_bop !== null ? $data->porcentaje_bop.'%' : '—' }}</strong>
        &nbsp;|&nbsp; PD promedio: <strong>{{ $data->promedio_pd !== null ? $data->promedio_pd.' mm' : '—' }}</strong>
        &nbsp;|&nbsp; Piezas PD ≥ 5 mm: <strong>{{ $data->piezas_pd_ge_5 ?? 0 }}</strong>
    </div>

    @php
        $sitios = ['DV','V','MV','DL','L','ML'];
        $dientes = collect($data->dientes ?? []);
        $filas = [
            'sup' => [18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28],
            'inf' => [48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38],
        ];
    @endphp

    @foreach($filas as $label => $nums)
        <p style="font-weight:bold;margin:6px 0 3px;">{{ $label === 'sup' ? 'Arcada superior' : 'Arcada inferior' }} — profundidades (mm)</p>
        <table>
            <thead>
                <tr>
                    <th>Sitio</th>
                    @foreach($nums as $n)<th>{{ $n }}</th>@endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($sitios as $si => $nombre)
                    <tr>
                        <td>{{ $nombre }}</td>
                        @foreach($nums as $n)
                            @php
                                $d = $dientes->firstWhere('numero', $n);
                                $val = $d['ausente'] ?? false ? '—' : ($d['pd'][$si] ?? '');
                                $cls = is_numeric($val) && $val >= 5 ? 'red' : '';
                            @endphp
                            <td class="{{ $cls }}">{{ $val === '' || $val === null ? '' : $val }}</td>
                        @endforeach
                    </tr>
                @endforeach
                <tr>
                    <td>Mov.</td>
                    @foreach($nums as $n)
                        @php $d = $dientes->firstWhere('numero', $n); @endphp
                        <td>{{ !empty($d['ausente']) ? '—' : ($d['movilidad'] ?? 0) }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    @endforeach

    @if($data->diagnostico)
        <p><strong>Diagnóstico:</strong> {{ $data->diagnostico }}</p>
    @endif
    @if($data->observaciones)
        <p><strong>Observaciones:</strong> {{ $data->observaciones }}</p>
    @endif

    <p class="foot">Sitios: DV/V/MV = vestibular distal/medio/mesial · DL/L/ML = lingual/palatino. Rojo = PD ≥ 5 mm.</p>
</body>
</html>
