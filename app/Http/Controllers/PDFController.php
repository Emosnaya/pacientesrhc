<?php

namespace App\Http\Controllers;

use App\Models\Clinico;
use App\Models\Esfuerzo;
use App\Models\Estratificacion;
use App\Models\Paciente;
use App\Models\ReporteFinal;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;

class PDFController extends Controller
{
    public function esfuerzoPdf(Request $request)
    {
        $data = Esfuerzo::find($request->id);
        $paciente =  Paciente::find($data->paciente_id);
        $user = User::find($data->user_id);

        $pdf = Pdf::loadView('esfuerzo', compact('data', 'paciente','user')); // Cargar vista PDF con datos

        return $pdf->stream('pe_Esfuerzo.pdf'); // Descargar el PDF
    }

    public function estratificacionPdf(Request $request)
    {
        $data = Estratificacion::find($request->id);
        $paciente =  Paciente::find($data->paciente_id);
        $user = User::find($data->user_id);

        $pdf = Pdf::loadView('estrati', compact('data', 'paciente','user'));
        return $pdf->stream('Estratificacion.pdf'); 
    }

    public function clinicoPdf(Request $request)
    {
        $data = Clinico::find($request->id);
        $paciente =  Paciente::find($data->paciente_id);
        $user = User::find($data->user_id);

        $pdf = Pdf::loadView('clinico', compact('data', 'paciente','user'));
        return $pdf->stream('Clinico.pdf'); 
    }
    public function reportePdf(Request $request)
    {
        $data = ReporteFinal::find($request->id);
        $esfuerzoUno = Esfuerzo::find($data->pe_1);
        $esfuerzoDos = Esfuerzo::find($data->pe_2);
        $paciente =  Paciente::find($data->paciente_id);
        $user = User::find($data->user_id);
        $estrati = Estratificacion::where('paciente_id', $paciente->id)->get();

        $pdf = Pdf::loadView('reporte', compact('data', 'paciente','user', 'estrati', 'esfuerzoUno', 'esfuerzoDos'));
        return $pdf->stream('Reporte_Final.pdf'); 
    }
}
