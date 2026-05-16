<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seguimiento Nutricional</title>
    <style>
        @font-face { font-family: 'DejaVu Sans'; src: url('{{ storage_path('fonts/DejaVuSans.ttf') }}'); }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1e293b; margin: 20px 24px; }
        .header { background: {!! $clinica->color_principal ?? '#0A1628' !!}; color: #fff; padding: 10px 12px; border-radius: 8px; margin-bottom: 10px; }
        .header-table, .info-table, .tbl { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; }
        .logo { width: 48px; height: 48px; background: #fff; border-radius: 6px; text-align: center; padding: 4px; }
        .logo img { max-width: 38px; max-height: 38px; }
        .title { font-size: 15px; font-weight: 700; }
        .sub { font-size: 9px; color: #94a3b8; }
        .badge { text-align: right; }
        .badge-box { display: inline-block; background: rgba(255,255,255,.14); padding: 5px 8px; border-radius: 5px; }
        .card { border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; padding: 8px 10px; margin-bottom: 10px; }
        .patient-name { font-size: 12px; font-weight: 700; color: {!! $clinica->color_principal ?? '#0A1628' !!}; margin-bottom: 5px; }
        .label { color: #64748b; font-size: 8px; }
        .value { font-weight: 700; color: #334155; }
        .section { margin-bottom: 10px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; }
        .section-title { background: {!! $clinica->color_principal ?? '#0A1628' !!}; color: #fff; font-size: 9px; font-weight: 700; padding: 4px 8px; text-transform: uppercase; }
        .section-body { padding: 8px; }
        .subsection { color: {!! $clinica->color_principal ?? '#0A1628' !!}; font-weight: 700; margin: 6px 0 4px; }
        .tbl th, .tbl td { border: 1px solid #e2e8f0; padding: 3px 5px; font-size: 8.5px; }
        .tbl th { background: #f1f5f9; color: #334155; }
        .txt { border: 1px solid #e2e8f0; background: #f8fafc; padding: 5px 7px; min-height: 18px; border-radius: 4px; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 4px 14px; border-top: 2px solid {!! $clinica->color_principal ?? '#0A1628' !!}; font-size: 8px; background: #fff; }
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer-name { font-weight: 700; color: {!! $clinica->color_principal ?? '#0A1628' !!}; }
        .footer-contact { text-align: right; color: #64748b; }
        .content-wrap { padding-bottom: 42px; }
        .firma { margin-top: 18px; text-align: center; }
        .firma img { max-height: 60px; }
        .line { width: 180px; border-top: 1px solid #334155; margin: 4px auto 0; }
        .firma-meta { font-size: 8px; color: #64748b; }
    </style>
</head>
<body>
@php
$bio = $seguimiento->valoracion_bioquimica ?? [];
$diet = $seguimiento->valoracion_dietetica ?? [];
$rec24 = $seguimiento->recordatorio_24h ?? [];
$analisis = $seguimiento->analisis_dieta_habitual ?? [];
$interv = $seguimiento->intervencion_nutricional ?? [];
$profesionalNombre = trim(($user->nombre_con_titulo ?? '') ?: (($user->nombre ?? '') . ' ' . ($user->apellidoPat ?? '') . ' ' . ($user->apellidoMat ?? '')));
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
    <table class="header-table"><tr>
        @if($clinicaLogo)<td style="width:60px"><div class="logo"><img src="{{ $clinicaLogo }}" alt="Logo"></div></td>@endif
        <td><div class="title">Seguimiento Nutricional</div><div class="sub">Valoración dietética, bioquímica e intervención nutricional</div></td>
        <td class="badge"><div class="badge-box">Seguimiento {{ $seguimiento->numero_seguimiento ?? 'S/N' }}<br>{{ $seguimiento->fecha_elaboracion?->format('d/m/Y') }}</div></td>
    </tr></table>
</div>
<div class="card">
    <div class="patient-name">{{ $paciente->nombre ?? '' }} {{ $paciente->apellidoPat ?? '' }} {{ $paciente->apellidoMat ?? '' }}</div>
    <table class="info-table"><tr>
        <td><div class="label">Registro</div><div class="value">{{ $paciente->registro ?? 'N/A' }}</div></td>
        <td><div class="label">Edad</div><div class="value">{{ $paciente->edad ?? 'N/A' }}</div></td>
        <td><div class="label">Sexo</div><div class="value">{{ $paciente->genero ?? 'N/A' }}</div></td>
        <td><div class="label">Profesional responsable</div><div class="value">{{ $profesionalNombre ?: 'N/A' }}</div></td>
    </tr></table>
</div>
<div class="section"><div class="section-title">Valoración bioquímica</div><div class="section-body">
    <div class="subsection">Química sanguínea</div>
    <table class="tbl">
        <tr><th>Mecanismo</th><th>Laboratorio</th><th>Accu-check</th><th>Accutrend</th></tr>
        <tr><td>{{ $bio['quimica_sanguinea']['mecanismo_toma'] ?? '—' }}</td><td>{{ !empty($bio['quimica_sanguinea']['laboratorio']) ? 'Sí' : 'No' }}</td><td>{{ !empty($bio['quimica_sanguinea']['accucheck']) ? 'Sí' : 'No' }}</td><td>{{ !empty($bio['quimica_sanguinea']['accutrend']) ? 'Sí' : 'No' }}</td></tr>
    </table>
    <table class="tbl" style="margin-top:6px;">
        <tr><th>Fecha/Hora</th><th>Glucosa</th><th>Colesterol total</th><th>Triglicéridos</th><th>Lactato</th><th>CT HDL</th><th>CT LDL</th><th>Albúmina</th><th>Prealbúmina</th><th>Transferrina</th><th>Ácido úrico</th><th>Urea</th><th>Creatinina</th><th>Sodio</th><th>Calcio</th><th>Fósforo</th><th>Otros</th></tr>
        @foreach(($bio['quimica_sanguinea']['muestras'] ?? []) as $row)
        <tr>
            <td>{{ $row['fecha_hora'] ?? '—' }}</td><td>{{ $row['glucosa'] ?? '—' }}</td><td>{{ $row['colesterol_total'] ?? '—' }}</td><td>{{ $row['trigliceridos'] ?? '—' }}</td><td>{{ $row['lactato'] ?? '—' }}</td><td>{{ $row['ct_hdl'] ?? '—' }}</td><td>{{ $row['ct_ldl'] ?? '—' }}</td><td>{{ $row['albumina'] ?? '—' }}</td><td>{{ $row['prealbumina'] ?? '—' }}</td><td>{{ $row['transferrina'] ?? '—' }}</td><td>{{ $row['acido_urico'] ?? '—' }}</td><td>{{ $row['urea'] ?? '—' }}</td><td>{{ $row['creatinina'] ?? '—' }}</td><td>{{ $row['sodio'] ?? '—' }}</td><td>{{ $row['calcio'] ?? '—' }}</td><td>{{ $row['fosforo'] ?? '—' }}</td><td>{{ $row['otros'] ?? '—' }}</td>
        </tr>
        @endforeach
    </table>
    <div class="subsection">Biometría hemática</div>
    <table class="tbl"><tr><th>Fecha</th><th>Cuenta eritrocitos</th><th>Hemoglobina</th><th>Hematocrito</th><th>Cuenta de leucocitos</th><th>Otro</th></tr>
        @foreach(($bio['biometria_hematica'] ?? []) as $row)
        <tr><td>{{ $row['fecha'] ?? '—' }}</td><td>{{ $row['cuenta_eritrocitos'] ?? '—' }}</td><td>{{ $row['hemoglobina'] ?? '—' }}</td><td>{{ $row['hematocrito'] ?? '—' }}</td><td>{{ $row['cuenta_leucocitos'] ?? '—' }}</td><td>{{ $row['otro'] ?? '—' }}</td></tr>
        @endforeach
    </table>
</div></div>
<div class="section"><div class="section-title">Valoración dietética</div><div class="section-body">
    <table class="tbl">
        <tr><th>Dieta especial</th><th>Veces</th><th>Razón</th><th>Tipo</th><th>Tiempo</th><th>Hace cuánto</th><th>Constante</th><th>Resultados</th></tr>
        <tr><td>{{ !empty($diet['dieta_especial']['si']) ? 'Sí' : 'No' }}</td><td>{{ $diet['dieta_especial']['veces'] ?? '—' }}</td><td>{{ $diet['dieta_especial']['razon'] ?? '—' }}</td><td>{{ $diet['dieta_especial']['tipo'] ?? '—' }}</td><td>{{ $diet['dieta_especial']['tiempo'] ?? '—' }}</td><td>{{ $diet['dieta_especial']['hace_cuanto'] ?? '—' }}</td><td>{{ !empty($diet['dieta_especial']['constante']) ? 'Sí' : 'No' }}</td><td>{{ !empty($diet['dieta_especial']['resultados']) ? 'Sí' : 'No' }}</td></tr>
    </table>
    <div class="subsection">Hábitos alimentarios</div>
    <div class="txt">Medicamentos para bajar de peso: {{ !empty($diet['medicamentos_bajar_peso']['si']) ? 'Sí' : 'No' }}. ¿Cuáles?: {{ $diet['medicamentos_bajar_peso']['cuales'] ?? '—' }}</div>
    <div class="txt" style="margin-top:6px;">Comidas al día: {{ $diet['comidas_dia'] ?? '—' }}. ¿Cuáles?: {{ $diet['cuales_comidas'] ?? '—' }}</div>
    <table class="tbl" style="margin-top:6px;">
        <tr><th>Frecuencia</th><th>Casa tiempo</th><th>Casa horario</th><th>Fuera tiempo</th><th>Fuera horario</th></tr>
        @foreach(($diet['frecuencia_comidas'] ?? []) as $row)
        <tr><td>{{ $row['frecuencia'] ?? '—' }}</td><td>{{ $row['casa_tiempo'] ?? '—' }}</td><td>{{ $row['casa_horario'] ?? '—' }}</td><td>{{ $row['fuera_tiempo'] ?? '—' }}</td><td>{{ $row['fuera_horario'] ?? '—' }}</td></tr>
        @endforeach
    </table>
    <div class="txt" style="margin-top:6px;">Preparación alimentos: {{ $diet['quien_prepara_alimentos'] ?? '—' }} | Entre comidas: {{ !empty($diet['come_entre_comidas']['si']) ? 'Sí' : 'No' }} {{ $diet['come_entre_comidas']['que'] ?? '' }}</div>
    <div class="txt" style="margin-top:6px;">Cambios últimos 6 meses: {{ !empty($diet['modificacion_alimentacion']['si']) ? 'Sí' : 'No' }} | ¿Por qué?: {{ $diet['modificacion_alimentacion']['porque'] ?? '—' }} | ¿Cómo?: {{ $diet['modificacion_alimentacion']['como'] ?? '—' }}</div>
    <div class="txt" style="margin-top:6px;">Apetito: {{ $diet['apetito'] ?? '—' }} | Hora de más hambre: {{ $diet['hora_mas_hambre'] ?? '—' }}</div>
    <div class="txt" style="margin-top:6px;">Agua al día: {{ $diet['agua_litros_dia'] ?? '—' }} | Frutas/día: {{ $diet['frutas_dia'] ?? '—' }} | Verduras/día: {{ $diet['verduras_dia'] ?? '—' }}</div>
    <div class="txt" style="margin-top:6px;">Fritos: {{ $diet['fritos_frecuencia'] ?? '—' }} | Dulces: {{ $diet['dulces_frecuencia'] ?? '—' }} | Bebidas azucaradas: {{ $diet['bebidas_azucar_frecuencia'] ?? '—' }}</div>
    <div class="txt" style="margin-top:6px;">Suplemento alimenticio: {{ !empty($diet['suplemento']['si']) ? 'Sí' : 'No' }} {{ $diet['suplemento']['cual'] ?? '' }}</div>
    <div class="txt" style="margin-top:6px;">Alimentos preferidos: {{ $diet['alimentos_preferidos'] ?? '—' }}</div>
    <div class="txt" style="margin-top:6px;">Alimentos que no le agradan/no acostumbra: {{ $diet['alimentos_no_agradables'] ?? '—' }}</div>
    <div class="txt" style="margin-top:6px;">Consumo varía con tristeza/nerviosismo/ansiedad: {{ !empty($diet['consumo_varia_emociones']['si']) ? 'Sí' : 'No' }} | ¿Cómo?: {{ $diet['consumo_varia_emociones']['como'] ?? '—' }}</div>
</div></div>
<div class="section"><div class="section-title">Recordatorio de 24 horas</div><div class="section-body">
    <table class="tbl"><tr><th>Tiempo comida</th><th>Platillo</th><th>Alimento/ingrediente</th><th>Cantidad</th><th>Equivalentes</th><th>HCO</th><th>Proteínas</th><th>Lípidos</th><th>Kcal</th></tr>
    @foreach(($rec24['filas'] ?? []) as $row)
        <tr><td>{{ $row['tiempo_comida'] ?? '—' }}</td><td>{{ $row['platillo'] ?? '—' }}</td><td>{{ $row['alimento'] ?? '—' }}</td><td>{{ $row['cantidad'] ?? '—' }}</td><td>{{ $row['equivalentes'] ?? '—' }}</td><td>{{ $row['hco'] ?? '—' }}</td><td>{{ $row['proteinas'] ?? '—' }}</td><td>{{ $row['lipidos'] ?? '—' }}</td><td>{{ $row['kcal'] ?? '—' }}</td></tr>
    @endforeach</table>
    <div class="txt" style="margin-top:6px;">Consumo total: HCO {{ $rec24['consumo_total']['hco'] ?? '—' }}, Proteínas {{ $rec24['consumo_total']['proteinas'] ?? '—' }}, Lípidos {{ $rec24['consumo_total']['lipidos'] ?? '—' }}, Kcal {{ $rec24['consumo_total']['kcal'] ?? '—' }}</div>
</div></div>
<div class="section"><div class="section-title">Análisis de dieta habitual</div><div class="section-body">
    <div class="subsection">Estimación de consumo por nutrimentos</div>
    <table class="tbl"><tr><th>Nutrimento</th><th>%</th><th>Kcal</th><th>Gramos</th><th>g/kg/día</th></tr>
        @foreach(($analisis['estimacion_consumo'] ?? []) as $row)
        <tr><td>{{ $row['nutrimento'] ?? '—' }}</td><td>{{ $row['porcentaje'] ?? '—' }}</td><td>{{ $row['kilocalorias'] ?? '—' }}</td><td>{{ $row['gramos'] ?? '—' }}</td><td>{{ $row['gkg'] ?? '—' }}</td></tr>
        @endforeach
    </table>
    <div class="txt" style="margin-top:6px;">Peso ideal: {{ $analisis['peso_ideal'] ?? '—' }} | Justificación: {{ $analisis['justificacion'] ?? '—' }}</div>
    <div class="txt" style="margin-top:6px;">GET/día: {{ $analisis['get_dia']['metabolismo_basal'] ?? '—' }} + AF {{ $analisis['get_dia']['af'] ?? '—' }} = {{ $analisis['get_dia']['total'] ?? '—' }}</div>
    <div class="txt" style="margin-top:6px;">Fórmula utilizada: {{ $analisis['formula_utilizada'] ?? '—' }}</div>
    <div class="txt" style="margin-top:6px;">Desarrollo del cálculo: {{ $analisis['desarrollo_calculo'] ?? '—' }}</div>
    <div class="subsection">Cálculo de requerimiento por nutrimentos</div>
    <table class="tbl"><tr><th>Nutrimento</th><th>%</th><th>Kcal</th><th>Gramos</th><th>g/kg/día</th></tr>
        @foreach(($analisis['calculo_requerimiento'] ?? []) as $row)
        <tr><td>{{ $row['nutrimento'] ?? '—' }}</td><td>{{ $row['porcentaje'] ?? '—' }}</td><td>{{ $row['kilocalorias'] ?? '—' }}</td><td>{{ $row['gramos'] ?? '—' }}</td><td>{{ $row['gkg'] ?? '—' }}</td></tr>
        @endforeach
    </table>
    <div class="subsection">Evaluación de la dieta habitual</div>
    <table class="tbl"><tr><th>Nutrimento</th><th>Dieta habitual</th><th>Requerimiento</th><th>% adecuación</th><th>Diagnóstico</th></tr>
        @foreach(($analisis['evaluacion_dieta'] ?? []) as $row)
        <tr><td>{{ $row['nutrimento'] ?? '—' }}</td><td>{{ $row['dieta_habitual'] ?? '—' }}</td><td>{{ $row['requerimiento'] ?? '—' }}</td><td>{{ $row['adecuacion'] ?? '—' }}</td><td>{{ $row['diagnostico'] ?? '—' }}</td></tr>
        @endforeach
    </table>
</div></div>
<div class="section"><div class="section-title">Intervención nutricional</div><div class="section-body">
    <div class="subsection">Diagnósticos PES</div>
    <table class="tbl"><tr><th>Problema</th><th>Etiología</th><th>Signos y síntomas</th></tr>
        @foreach(($interv['diagnosticos_pes'] ?? []) as $row)
        <tr><td>{{ $row['problema'] ?? '—' }}</td><td>{{ $row['etiologia'] ?? '—' }}</td><td>{{ $row['signos_sintomas'] ?? '—' }}</td></tr>
        @endforeach
    </table>
    <div class="subsection">Plan de alimentación</div>
    <table class="tbl"><tr><th>Grupo</th><th>Subgrupo</th><th>Raciones</th><th>Energía</th><th>Proteínas</th><th>Lípidos</th><th>HCO</th></tr>
        @foreach(($interv['plan_alimentacion'] ?? []) as $row)
        <tr><td>{{ $row['grupo'] ?? '—' }}</td><td>{{ $row['subgrupo'] ?? '—' }}</td><td>{{ $row['raciones'] ?? '—' }}</td><td>{{ $row['energia'] ?? '—' }}</td><td>{{ $row['proteinas'] ?? '—' }}</td><td>{{ $row['lipidos'] ?? '—' }}</td><td>{{ $row['hidratos'] ?? '—' }}</td></tr>
        @endforeach
    </table>
    <div class="subsection">Equivalentes por día</div>
    <table class="tbl"><tr><th>Grupo</th><th>Subgrupo</th><th>Total</th><th>Desayuno</th><th>Colación</th><th>Comida</th><th>Colación 2</th><th>Cena</th></tr>
        @foreach(($interv['equivalentes_por_dia'] ?? []) as $row)
        <tr><td>{{ $row['grupo'] ?? '—' }}</td><td>{{ $row['subgrupo'] ?? '—' }}</td><td>{{ $row['totales'] ?? '—' }}</td><td>{{ $row['desayuno'] ?? '—' }}</td><td>{{ $row['colacion_1'] ?? '—' }}</td><td>{{ $row['comida'] ?? '—' }}</td><td>{{ $row['colacion_2'] ?? '—' }}</td><td>{{ $row['cena'] ?? '—' }}</td></tr>
        @endforeach
    </table>
    <div class="subsection">Tratamiento nutricional</div>
    <table class="tbl"><tr><th>Fecha</th><th>Tipo de dieta</th><th>Requerimiento diario</th><th>Hábito a trabajar</th></tr>
        @foreach(($interv['tratamiento_nutricional'] ?? []) as $row)
        <tr><td>{{ $row['fecha'] ?? '—' }}</td><td>{{ $row['tipo_dieta'] ?? '—' }}</td><td>{{ $row['requerimiento_diario'] ?? '—' }}</td><td>{{ $row['habito_trabajar'] ?? '—' }}</td></tr>
        @endforeach
    </table>
    @if(!empty($seguimiento->observaciones))<div class="txt" style="margin-top:6px;">Observaciones: {{ $seguimiento->observaciones }}</div>@endif
</div></div>
<div class="firma">
    @if($firmaBase64)<img src="{{ $firmaBase64 }}" alt="Firma">@endif
    <div class="line"></div>
    <div><strong>{{ $profesionalNombre ?: 'Profesional responsable' }}</strong></div>
    @if($user->cedula_especialista ?? null)<div class="firma-meta">Cédula: {{ $user->cedula_especialista }}</div>@endif
    <div class="firma-meta">{{ $profesionalRol }}</div>
</div>
</div>
</body>
</html>
