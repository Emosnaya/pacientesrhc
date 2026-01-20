<?php

namespace App\Http\Controllers;

use App\Models\PruebaEsfuerzoPulmonar;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class PruebaEsfuerzoPulmonarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $pacienteId = $request->query('paciente_id');

        if (!$pacienteId) {
            return response()->json(['error' => 'paciente_id is required'], 400);
        }

        $pruebas = PruebaEsfuerzoPulmonar::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_id)
            ->with(['user', 'paciente'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($pruebas);
    }

    /**
     * Get all pruebas de esfuerzo pulmonar for a specific patient
     */
    public function getByPaciente($pacienteId)
    {
        $user = Auth::user();
        
        // Verificar que el paciente existe y pertenece a la misma clínica
        $paciente = Paciente::findOrFail($pacienteId);
        if ($paciente->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'No tienes acceso a este paciente'], 403);
        }

        $pruebas = PruebaEsfuerzoPulmonar::where('paciente_id', $pacienteId)
            ->where('clinica_id', $user->clinica_id)
            ->with(['user', 'paciente'])
            ->orderBy('fecha_realizacion', 'desc')
            ->get();

        return response()->json($pruebas);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha_realizacion' => 'required|date',
            'diagnosticos' => 'nullable|string',
            'oxigeno_suplementario' => 'nullable|boolean',
            'oxigeno_litros' => 'nullable|numeric',
            'predicho_vo2' => 'nullable|numeric',
            'predicho_mets' => 'nullable|numeric',
            'fcmax_100' => 'nullable|integer',
            'fcmax_65' => 'nullable|integer',
            'fcmax_75' => 'nullable|integer',
            'fcmax_85' => 'nullable|integer',
            'basal_fc' => 'nullable|integer',
            'basal_tas' => 'nullable|integer',
            'basal_tad' => 'nullable|integer',
            'basal_saturacion' => 'nullable|integer',
            'basal_borg_disnea' => 'nullable|integer',
            'basal_borg_fatiga' => 'nullable|integer',
            'max_fc_pico' => 'nullable|integer',
            'max_tas' => 'nullable|integer',
            'max_tad' => 'nullable|integer',
            'max_saturacion' => 'nullable|integer',
            'max_borg_disnea' => 'nullable|integer',
            'max_borg_fatiga' => 'nullable|integer',
            'max_velocidad_kmh' => 'nullable|numeric',
            'max_inclinacion' => 'nullable|numeric',
            'max_mets' => 'nullable|numeric',
            'motivo_detencion' => 'nullable|string',
            'rec1_fc' => 'nullable|integer',
            'rec1_tas' => 'nullable|integer',
            'rec1_tad' => 'nullable|integer',
            'rec1_saturacion' => 'nullable|integer',
            'rec1_borg_disnea' => 'nullable|integer',
            'rec1_borg_fatiga' => 'nullable|integer',
            'rec3_fc' => 'nullable|integer',
            'rec3_tas' => 'nullable|integer',
            'rec3_tad' => 'nullable|integer',
            'rec3_saturacion' => 'nullable|integer',
            'rec3_borg_disnea' => 'nullable|integer',
            'rec3_borg_fatiga' => 'nullable|integer',
            'rec5_fc' => 'nullable|integer',
            'rec5_tas' => 'nullable|integer',
            'rec5_tad' => 'nullable|integer',
            'rec5_saturacion' => 'nullable|integer',
            'rec5_borg_disnea' => 'nullable|integer',
            'rec5_borg_fatiga' => 'nullable|integer',
            'etapa_maxima' => 'nullable|string',
            'vel_etapa' => 'nullable|numeric',
            'inclinacion_etapa' => 'nullable|numeric',
            'mets_equivalentes' => 'nullable|numeric',
            'vo2_equivalente' => 'nullable|numeric',
            'fc_pico_alcanzado' => 'nullable|integer',
            'saturacion_minima' => 'nullable|integer',
            'borg_max_disnea' => 'nullable|integer',
            'borg_max_fatiga' => 'nullable|integer',
            'porcentaje_fcmax' => 'nullable|numeric',
            'porcentaje_mets' => 'nullable|numeric',
            'porcentaje_vo2' => 'nullable|numeric',
            'clase_funcional' => 'nullable|string',
            'interpretacion' => 'nullable|string',
            'plan_sesiones' => 'nullable|integer',
            'plan_tiempo' => 'nullable|string',
            'plan_oxigeno_litros' => 'nullable|numeric',
            'plan_borg_modificado' => 'nullable|string',
            'plan_fc_inicial_min' => 'nullable|integer',
            'plan_fc_inicial_max' => 'nullable|integer',
            'plan_fc_no_rebasar' => 'nullable|integer',
            'plan_banda_sin_fin' => 'nullable|boolean',
            'plan_ergometro_brazos' => 'nullable|boolean',
            'plan_bicicleta_estatica' => 'nullable|boolean',
            'plan_banda_velocidad' => 'nullable|numeric',
            'plan_banda_tiempo' => 'nullable|string',
            'plan_banda_resistencia' => 'nullable|string',
            'plan_ergometro_velocidad' => 'nullable|string',
            'plan_ergometro_resistencia' => 'nullable|string',
            'plan_bicicleta_velocidad' => 'nullable|string',
            'plan_bicicleta_resistencia' => 'nullable|string',
            'plan_manejo_complementario' => 'nullable|string',
        ]);

        $validated['user_id'] = $user->id;
        $validated['clinica_id'] = $user->clinica_id;
        $validated['tipo_exp'] = 16;

        // Obtener paciente para cálculos automáticos
        $paciente = Paciente::find($validated['paciente_id']);
        
        // Realizar cálculos automáticos
        $validated = $this->calcularValoresAutomaticos($validated, $paciente);

        $prueba = PruebaEsfuerzoPulmonar::create($validated);

        return response()->json($prueba->load(['user', 'paciente']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PruebaEsfuerzoPulmonar $pruebaEsfuerzoPulmonar)
    {
        $user = Auth::user();

        if ($pruebaEsfuerzoPulmonar->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($pruebaEsfuerzoPulmonar->load(['user', 'paciente']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PruebaEsfuerzoPulmonar $pruebaEsfuerzoPulmonar)
    {
        $user = Auth::user();

        if ($pruebaEsfuerzoPulmonar->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'fecha_realizacion' => 'nullable|date',
            'diagnosticos' => 'nullable|string',
            'oxigeno_suplementario' => 'nullable|boolean',
            'oxigeno_litros' => 'nullable|numeric',
            'predicho_vo2' => 'nullable|numeric',
            'predicho_mets' => 'nullable|numeric',
            'fcmax_100' => 'nullable|integer',
            'fcmax_65' => 'nullable|integer',
            'fcmax_75' => 'nullable|integer',
            'fcmax_85' => 'nullable|integer',
            'basal_fc' => 'nullable|integer',
            'basal_tas' => 'nullable|integer',
            'basal_tad' => 'nullable|integer',
            'basal_saturacion' => 'nullable|integer',
            'basal_borg_disnea' => 'nullable|integer',
            'basal_borg_fatiga' => 'nullable|integer',
            'max_fc_pico' => 'nullable|integer',
            'max_tas' => 'nullable|integer',
            'max_tad' => 'nullable|integer',
            'max_saturacion' => 'nullable|integer',
            'max_borg_disnea' => 'nullable|integer',
            'max_borg_fatiga' => 'nullable|integer',
            'max_velocidad_kmh' => 'nullable|numeric',
            'max_inclinacion' => 'nullable|numeric',
            'max_mets' => 'nullable|numeric',
            'motivo_detencion' => 'nullable|string',
            'rec1_fc' => 'nullable|integer',
            'rec1_tas' => 'nullable|integer',
            'rec1_tad' => 'nullable|integer',
            'rec1_saturacion' => 'nullable|integer',
            'rec1_borg_disnea' => 'nullable|integer',
            'rec1_borg_fatiga' => 'nullable|integer',
            'rec3_fc' => 'nullable|integer',
            'rec3_tas' => 'nullable|integer',
            'rec3_tad' => 'nullable|integer',
            'rec3_saturacion' => 'nullable|integer',
            'rec3_borg_disnea' => 'nullable|integer',
            'rec3_borg_fatiga' => 'nullable|integer',
            'rec5_fc' => 'nullable|integer',
            'rec5_tas' => 'nullable|integer',
            'rec5_tad' => 'nullable|integer',
            'rec5_saturacion' => 'nullable|integer',
            'rec5_borg_disnea' => 'nullable|integer',
            'rec5_borg_fatiga' => 'nullable|integer',
            'etapa_maxima' => 'nullable|string',
            'vel_etapa' => 'nullable|numeric',
            'inclinacion_etapa' => 'nullable|numeric',
            'mets_equivalentes' => 'nullable|numeric',
            'vo2_equivalente' => 'nullable|numeric',
            'fc_pico_alcanzado' => 'nullable|integer',
            'saturacion_minima' => 'nullable|integer',
            'borg_max_disnea' => 'nullable|integer',
            'borg_max_fatiga' => 'nullable|integer',
            'porcentaje_fcmax' => 'nullable|numeric',
            'porcentaje_mets' => 'nullable|numeric',
            'porcentaje_vo2' => 'nullable|numeric',
            'clase_funcional' => 'nullable|string',
            'interpretacion' => 'nullable|string',
            'plan_sesiones' => 'nullable|integer',
            'plan_tiempo' => 'nullable|string',
            'plan_oxigeno_litros' => 'nullable|numeric',
            'plan_borg_modificado' => 'nullable|string',
            'plan_fc_inicial_min' => 'nullable|integer',
            'plan_fc_inicial_max' => 'nullable|integer',
            'plan_fc_no_rebasar' => 'nullable|integer',
            'plan_banda_sin_fin' => 'nullable|boolean',
            'plan_ergometro_brazos' => 'nullable|boolean',
            'plan_bicicleta_estatica' => 'nullable|boolean',
            'plan_banda_velocidad' => 'nullable|numeric',
            'plan_banda_tiempo' => 'nullable|string',
            'plan_banda_resistencia' => 'nullable|string',
            'plan_ergometro_velocidad' => 'nullable|string',
            'plan_ergometro_resistencia' => 'nullable|string',
            'plan_bicicleta_velocidad' => 'nullable|string',
            'plan_bicicleta_resistencia' => 'nullable|string',
            'plan_manejo_complementario' => 'nullable|string',
        ]);

        // Realizar cálculos automáticos
        $validated = $this->calcularValoresAutomaticos($validated, $pruebaEsfuerzoPulmonar->paciente);

        $pruebaEsfuerzoPulmonar->update($validated);

        return response()->json($pruebaEsfuerzoPulmonar->load(['user', 'paciente']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PruebaEsfuerzoPulmonar $pruebaEsfuerzoPulmonar)
    {
        $user = Auth::user();

        if ($pruebaEsfuerzoPulmonar->clinica_id !== $user->clinica_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $pruebaEsfuerzoPulmonar->delete();

        return response()->json(['message' => 'Prueba de esfuerzo pulmonar eliminada correctamente']);
    }

    /**
     * Generate PDF for the specified resource.
     */
    public function generatePDF($id)
    {
        $user = Auth::user();
        
        $prueba = PruebaEsfuerzoPulmonar::with(['paciente', 'user'])->findOrFail($id);

        if ($prueba->clinica_id !== $user->clinica_id) {
            abort(403, 'Unauthorized');
        }

        $data = $prueba;
        $paciente = $prueba->paciente;
        $usuario = $prueba->user;

        // Get firma if exists
        $firmaBase64 = null;
        if ($usuario->firma) {
            $firmaPath = storage_path('app/public/' . $usuario->firma);
            if (file_exists($firmaPath)) {
                $firmaData = file_get_contents($firmaPath);
                $firmaBase64 = 'data:image/png;base64,' . base64_encode($firmaData);
            }
        }

        $pdf = PDF::loadView('pruebaesfuerzopulmonar', compact('data', 'paciente', 'user', 'firmaBase64'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('prueba_esfuerzo_pulmonar_' . $paciente->registro . '.pdf');
    }

    /**
     * Download PDF for the specified resource.
     */
    public function downloadPDF($id)
    {
        $user = Auth::user();
        
        $prueba = PruebaEsfuerzoPulmonar::with(['paciente', 'user'])->findOrFail($id);

        if ($prueba->clinica_id !== $user->clinica_id) {
            abort(403, 'Unauthorized');
        }

        $data = $prueba;
        $paciente = $prueba->paciente;
        $usuario = $prueba->user;

        // Get firma if exists
        $firmaBase64 = null;
        if ($usuario->firma) {
            $firmaPath = storage_path('app/public/' . $usuario->firma);
            if (file_exists($firmaPath)) {
                $firmaData = file_get_contents($firmaPath);
                $firmaBase64 = 'data:image/png;base64,' . base64_encode($firmaData);
            }
        }

        $pdf = PDF::loadView('pruebaesfuerzopulmonar', compact('data', 'paciente', 'user', 'firmaBase64'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->download('prueba_esfuerzo_pulmonar_' . $paciente->registro . '.pdf');
    }

    /**
     * Calcular valores automáticos basados en fórmulas médicas
     */
    private function calcularValoresAutomaticos(array $data, $paciente)
    {
        // Calcular edad del paciente
        if ($paciente && $paciente->fechaNacimiento) {
            $fechaNacimiento = new \DateTime($paciente->fechaNacimiento);
            $hoy = new \DateTime();
            $edad = $hoy->diff($fechaNacimiento)->y;
            $sexo = strtolower($paciente->sexo ?? '');

            // 1. Predicho VO2
            // Hombres: 60-(0.55 x edad en años)
            // Mujeres: 48-(0.37 x edad en años)
            if (!isset($data['predicho_vo2']) || empty($data['predicho_vo2'])) {
                if (in_array($sexo, ['masculino', 'm', 'hombre'])) {
                    $data['predicho_vo2'] = round(60 - (0.55 * $edad), 2);
                } elseif (in_array($sexo, ['femenino', 'f', 'mujer'])) {
                    $data['predicho_vo2'] = round(48 - (0.37 * $edad), 2);
                }
            }

            // 2. Predicho METS: (predicho Vo2 / 3.5)
            if (!isset($data['predicho_mets']) || empty($data['predicho_mets'])) {
                if (isset($data['predicho_vo2']) && $data['predicho_vo2'] > 0) {
                    $data['predicho_mets'] = round($data['predicho_vo2'] / 3.5, 2);
                }
            }

            // 3. 100% FCMAX: (220-edad)
            if (!isset($data['fcmax_100']) || empty($data['fcmax_100'])) {
                $data['fcmax_100'] = 220 - $edad;
            }

            // 4. 65% FCMAX: (FCMAX X 0.65)
            if (!isset($data['fcmax_65']) || empty($data['fcmax_65'])) {
                if (isset($data['fcmax_100'])) {
                    $data['fcmax_65'] = round($data['fcmax_100'] * 0.65);
                }
            }

            // 5. 75% FC MAX: (FCMAX X 0.75)
            if (!isset($data['fcmax_75']) || empty($data['fcmax_75'])) {
                if (isset($data['fcmax_100'])) {
                    $data['fcmax_75'] = round($data['fcmax_100'] * 0.75);
                }
            }

            // 6. 85% FC MAX: (FCMAX X 0.85)
            if (!isset($data['fcmax_85']) || empty($data['fcmax_85'])) {
                if (isset($data['fcmax_100'])) {
                    $data['fcmax_85'] = round($data['fcmax_100'] * 0.85);
                }
            }
        }

        // 7. VO2 Equivalente de la Prueba: (METS Equivalentes de la Prueba X 3.5)
        if (isset($data['mets_equivalentes']) && $data['mets_equivalentes'] > 0) {
            $data['vo2_equivalente'] = round($data['mets_equivalentes'] * 3.5, 2);
        }

        // 8. %FCMAX: (FC Pico x 100 / (Resultado de 100% FCMAX))
        if (isset($data['fc_pico_alcanzado']) && isset($data['fcmax_100']) && $data['fcmax_100'] > 0) {
            $data['porcentaje_fcmax'] = round(($data['fc_pico_alcanzado'] * 100) / $data['fcmax_100'], 2);
        }

        // 9. %METS Alcanzados: (METS equivalentes de la prueba x 100 / Predicho METS)
        if (isset($data['mets_equivalentes']) && isset($data['predicho_mets']) && $data['predicho_mets'] > 0) {
            $data['porcentaje_mets'] = round(($data['mets_equivalentes'] * 100) / $data['predicho_mets'], 2);
        }

        // 10. %VO2 Alcanzado: (VO2 equivalentes de la prueba x 100 / Predicho VO2)
        if (isset($data['vo2_equivalente']) && isset($data['predicho_vo2']) && $data['predicho_vo2'] > 0) {
            $data['porcentaje_vo2'] = round(($data['vo2_equivalente'] * 100) / $data['predicho_vo2'], 2);
        }

        return $data;
    }
}
