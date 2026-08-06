<?php

namespace Database\Seeders;

use App\Models\Landmark;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LandmarksCdmxSeeder extends Seeder
{
    public function run(): void
    {
        $items = $this->cdmxLandmarks();

        foreach ($items as $index => $item) {
            $slug = $item['slug'] ?? Str::slug($item['nombre']);

            Landmark::updateOrCreate(
                ['slug' => $slug],
                [
                    'nombre' => $item['nombre'],
                    'tipo' => $item['tipo'] ?? 'hospital',
                    'ciudad' => $item['ciudad'] ?? 'Ciudad de México',
                    'alcaldia' => $item['alcaldia'] ?? null,
                    'estado' => $item['estado'] ?? 'CDMX',
                    'direccion' => $item['direccion'] ?? null,
                    'latitud' => $item['lat'],
                    'longitud' => $item['lng'],
                    'activo' => true,
                    'orden' => $item['orden'] ?? ($index + 1),
                ]
            );
        }
    }

    /**
     * Catálogo práctico de hospitales, clínicas y plazas médicas frecuentes en CDMX.
     * Coordenadas aproximadas del acceso principal / zona reconocible.
     */
    private function cdmxLandmarks(): array
    {
        return [
            // —— Hospitales privados grandes ——
            ['nombre' => 'Hospital Ángeles Pedregal', 'tipo' => 'hospital', 'alcaldia' => 'Álvaro Obregón', 'lat' => 19.3138, 'lng' => -99.2205, 'orden' => 10],
            ['nombre' => 'Hospital Ángeles Clínica Londres', 'tipo' => 'hospital', 'alcaldia' => 'Cuauhtémoc', 'lat' => 19.4245, 'lng' => -99.1678, 'orden' => 11],
            ['nombre' => 'Hospital Ángeles Metropolitano', 'tipo' => 'hospital', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4392, 'lng' => -99.2045, 'orden' => 12],
            ['nombre' => 'Hospital Ángeles Lindavista', 'tipo' => 'hospital', 'alcaldia' => 'Gustavo A. Madero', 'lat' => 19.4905, 'lng' => -99.1308, 'orden' => 13],
            ['nombre' => 'Hospital Ángeles Acoxpa', 'tipo' => 'hospital', 'alcaldia' => 'Tlalpan', 'lat' => 19.2975, 'lng' => -99.1452, 'orden' => 14],
            ['nombre' => 'Hospital Ángeles Lomas', 'tipo' => 'hospital', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.3985, 'lng' => -99.2458, 'orden' => 15],
            ['nombre' => 'Hospital Ángeles del Pedregal', 'tipo' => 'hospital', 'alcaldia' => 'Álvaro Obregón', 'lat' => 19.3142, 'lng' => -99.2210, 'orden' => 16],
            ['nombre' => 'Médica Sur', 'tipo' => 'hospital', 'alcaldia' => 'Tlalpan', 'lat' => 19.2958, 'lng' => -99.1625, 'orden' => 20],
            ['nombre' => 'Hospital ABC Observatorio', 'tipo' => 'hospital', 'alcaldia' => 'Álvaro Obregón', 'lat' => 19.3982, 'lng' => -99.1965, 'orden' => 30],
            ['nombre' => 'Hospital ABC Santa Fe', 'tipo' => 'hospital', 'alcaldia' => 'Cuajimalpa', 'lat' => 19.3595, 'lng' => -99.2582, 'orden' => 31],
            ['nombre' => 'Centro Médico ABC Campus Observatorio', 'tipo' => 'hospital', 'alcaldia' => 'Álvaro Obregón', 'lat' => 19.3980, 'lng' => -99.1968, 'orden' => 32],
            ['nombre' => 'Hospital Español', 'tipo' => 'hospital', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4278, 'lng' => -99.1925, 'orden' => 40],
            ['nombre' => 'Hospital Español de México', 'tipo' => 'hospital', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4275, 'lng' => -99.1928, 'orden' => 41],
            ['nombre' => 'Centro Médico ABC', 'tipo' => 'hospital', 'alcaldia' => 'Álvaro Obregón', 'lat' => 19.3985, 'lng' => -99.1970, 'orden' => 33],
            ['nombre' => 'Hospital San Ángel Inn Universidad', 'tipo' => 'hospital', 'alcaldia' => 'Coyoacán', 'lat' => 19.3325, 'lng' => -99.1855, 'orden' => 50],
            ['nombre' => 'Hospital San Ángel Inn Sur', 'tipo' => 'hospital', 'alcaldia' => 'Tlalpan', 'lat' => 19.3012, 'lng' => -99.1685, 'orden' => 51],
            ['nombre' => 'Hospital San Ángel Inn Chapultepec', 'tipo' => 'hospital', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4205, 'lng' => -99.1852, 'orden' => 52],
            ['nombre' => 'Hospital San Ángel Inn Satélite', 'tipo' => 'hospital', 'alcaldia' => 'Naucalpan', 'ciudad' => 'Estado de México', 'estado' => 'Edomex', 'lat' => 19.5105, 'lng' => -99.2345, 'orden' => 53],
            ['nombre' => 'Star Médica Centro', 'tipo' => 'hospital', 'alcaldia' => 'Cuauhtémoc', 'lat' => 19.4285, 'lng' => -99.1585, 'orden' => 60],
            ['nombre' => 'Star Médica Lomas Verdes', 'tipo' => 'hospital', 'alcaldia' => 'Naucalpan', 'ciudad' => 'Estado de México', 'estado' => 'Edomex', 'lat' => 19.5055, 'lng' => -99.2558, 'orden' => 61],
            ['nombre' => 'Hospital Dalinde', 'tipo' => 'hospital', 'alcaldia' => 'Benito Juárez', 'lat' => 19.3885, 'lng' => -99.1652, 'orden' => 70],
            ['nombre' => 'Hospital HMG Coyoacán', 'tipo' => 'hospital', 'alcaldia' => 'Coyoacán', 'lat' => 19.3458, 'lng' => -99.1625, 'orden' => 80],
            ['nombre' => 'Hospital HMG Coyoacán Sur', 'tipo' => 'hospital', 'alcaldia' => 'Coyoacán', 'lat' => 19.3385, 'lng' => -99.1585, 'orden' => 81],
            ['nombre' => 'Centro Médico Dalinde', 'tipo' => 'hospital', 'alcaldia' => 'Benito Juárez', 'lat' => 19.3882, 'lng' => -99.1655, 'orden' => 71],
            ['nombre' => 'Hospital Galenia', 'tipo' => 'hospital', 'alcaldia' => 'Benito Juárez', 'lat' => 19.3925, 'lng' => -99.1725, 'orden' => 90],
            ['nombre' => 'Hospital Juárez de México', 'tipo' => 'hospital', 'alcaldia' => 'Cuauhtémoc', 'lat' => 19.4395, 'lng' => -99.1455, 'orden' => 100],
            ['nombre' => 'Hospital de la Mujer', 'tipo' => 'hospital', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4485, 'lng' => -99.1852, 'orden' => 110],
            ['nombre' => 'Hospital Infantil de México Federico Gómez', 'tipo' => 'hospital', 'alcaldia' => 'Cuauhtémoc', 'lat' => 19.4155, 'lng' => -99.1525, 'orden' => 120],
            ['nombre' => 'Hospital General de México', 'tipo' => 'hospital', 'alcaldia' => 'Cuauhtémoc', 'lat' => 19.4125, 'lng' => -99.1515, 'orden' => 130],
            ['nombre' => 'Hospital General Dr. Manuel Gea González', 'tipo' => 'hospital', 'alcaldia' => 'Tlalpan', 'lat' => 19.3045, 'lng' => -99.1585, 'orden' => 140],
            ['nombre' => 'Instituto Nacional de Cardiología Ignacio Chávez', 'tipo' => 'hospital', 'alcaldia' => 'Tlalpan', 'lat' => 19.2915, 'lng' => -99.1555, 'orden' => 150],
            ['nombre' => 'Instituto Nacional de Ciencias Médicas y Nutrición Salvador Zubirán', 'tipo' => 'hospital', 'alcaldia' => 'Tlalpan', 'lat' => 19.2885, 'lng' => -99.1552, 'orden' => 160],
            ['nombre' => 'Instituto Nacional de Neurología y Neurocirugía', 'tipo' => 'hospital', 'alcaldia' => 'Tlalpan', 'lat' => 19.2925, 'lng' => -99.1585, 'orden' => 170],
            ['nombre' => 'Instituto Nacional de Pediatría', 'tipo' => 'hospital', 'alcaldia' => 'Coyoacán', 'lat' => 19.3055, 'lng' => -99.1825, 'orden' => 180],
            ['nombre' => 'Instituto Nacional de Cancerología', 'tipo' => 'hospital', 'alcaldia' => 'Tlalpan', 'lat' => 19.2928, 'lng' => -99.1625, 'orden' => 190],
            ['nombre' => 'Instituto Nacional de Enfermedades Respiratorias', 'tipo' => 'hospital', 'alcaldia' => 'Tlalpan', 'lat' => 19.2955, 'lng' => -99.1588, 'orden' => 200],
            ['nombre' => 'Centro Médico Nacional Siglo XXI', 'tipo' => 'hospital', 'alcaldia' => 'Cuauhtémoc', 'lat' => 19.4075, 'lng' => -99.1545, 'orden' => 210],
            ['nombre' => 'Hospital de Especialidades CMN Siglo XXI', 'tipo' => 'hospital', 'alcaldia' => 'Cuauhtémoc', 'lat' => 19.4082, 'lng' => -99.1548, 'orden' => 211],
            ['nombre' => 'Centro Médico Nacional La Raza', 'tipo' => 'hospital', 'alcaldia' => 'Azcapotzalco', 'lat' => 19.4855, 'lng' => -99.1455, 'orden' => 220],
            ['nombre' => 'Hospital de Cardiología CMN Siglo XXI', 'tipo' => 'hospital', 'alcaldia' => 'Cuauhtémoc', 'lat' => 19.4078, 'lng' => -99.1552, 'orden' => 212],
            ['nombre' => 'Hospital Regional Lic. Adolfo López Mateos', 'tipo' => 'hospital', 'alcaldia' => 'Álvaro Obregón', 'lat' => 19.3855, 'lng' => -99.1855, 'orden' => 230],
            ['nombre' => 'Hospital Central Militar', 'tipo' => 'hospital', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4385, 'lng' => -99.2155, 'orden' => 240],
            ['nombre' => 'Hospital Militar de Especialidades de la Mujer', 'tipo' => 'hospital', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4392, 'lng' => -99.2162, 'orden' => 241],
            ['nombre' => 'Hospital Pemex Picacho', 'tipo' => 'hospital', 'alcaldia' => 'Tlalpan', 'lat' => 19.3085, 'lng' => -99.1855, 'orden' => 250],
            ['nombre' => 'Hospital 20 de Noviembre ISSSTE', 'tipo' => 'hospital', 'alcaldia' => 'Benito Juárez', 'lat' => 19.3755, 'lng' => -99.1685, 'orden' => 260],
            ['nombre' => 'Hospital General Tacuba', 'tipo' => 'hospital', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4555, 'lng' => -99.1855, 'orden' => 270],
            ['nombre' => 'Hospital General Balbuena', 'tipo' => 'hospital', 'alcaldia' => 'Venustiano Carranza', 'lat' => 19.4255, 'lng' => -99.1155, 'orden' => 280],
            ['nombre' => 'Hospital General Xoco', 'tipo' => 'hospital', 'alcaldia' => 'Benito Juárez', 'lat' => 19.3685, 'lng' => -99.1655, 'orden' => 290],
            ['nombre' => 'Hospital General Ajusco Medio', 'tipo' => 'hospital', 'alcaldia' => 'Tlalpan', 'lat' => 19.2755, 'lng' => -99.1855, 'orden' => 300],
            ['nombre' => 'Hospital General Tláhuac', 'tipo' => 'hospital', 'alcaldia' => 'Tláhuac', 'lat' => 19.2855, 'lng' => -99.0455, 'orden' => 310],
            ['nombre' => 'Hospital General Villa', 'tipo' => 'hospital', 'alcaldia' => 'Gustavo A. Madero', 'lat' => 19.4855, 'lng' => -99.1155, 'orden' => 320],
            ['nombre' => 'Hospital Pediátrico de Coyoacán', 'tipo' => 'hospital', 'alcaldia' => 'Coyoacán', 'lat' => 19.3455, 'lng' => -99.1555, 'orden' => 330],
            ['nombre' => 'Hospital Pediátrico Legaria', 'tipo' => 'hospital', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4555, 'lng' => -99.1955, 'orden' => 340],
            ['nombre' => 'Hospital Infantil Privado', 'tipo' => 'hospital', 'alcaldia' => 'Benito Juárez', 'lat' => 19.3855, 'lng' => -99.1755, 'orden' => 350],

            // —— Clínicas y centros privados frecuentes ——
            ['nombre' => 'Clínica Londres', 'tipo' => 'clinica', 'alcaldia' => 'Cuauhtémoc', 'lat' => 19.4248, 'lng' => -99.1682, 'orden' => 400],
            ['nombre' => 'Clínica del Parque', 'tipo' => 'clinica', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4285, 'lng' => -99.1955, 'orden' => 410],
            ['nombre' => 'Clínica San Rafael', 'tipo' => 'clinica', 'alcaldia' => 'Cuauhtémoc', 'lat' => 19.4355, 'lng' => -99.1555, 'orden' => 420],
            ['nombre' => 'Clínica Lomas Altas', 'tipo' => 'clinica', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4055, 'lng' => -99.2355, 'orden' => 430],
            ['nombre' => 'Clínica Médica del Sur', 'tipo' => 'clinica', 'alcaldia' => 'Tlalpan', 'lat' => 19.2955, 'lng' => -99.1628, 'orden' => 440],
            ['nombre' => 'Centro Médico Polanco', 'tipo' => 'clinica', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4325, 'lng' => -99.1955, 'orden' => 450],
            ['nombre' => 'Centro Médico Interlomas', 'tipo' => 'clinica', 'alcaldia' => 'Huixquilucan', 'ciudad' => 'Estado de México', 'estado' => 'Edomex', 'lat' => 19.3985, 'lng' => -99.2855, 'orden' => 460],
            ['nombre' => 'Centro Médico Satélite', 'tipo' => 'clinica', 'alcaldia' => 'Naucalpan', 'ciudad' => 'Estado de México', 'estado' => 'Edomex', 'lat' => 19.5085, 'lng' => -99.2355, 'orden' => 470],
            ['nombre' => 'Hospital Belén', 'tipo' => 'hospital', 'alcaldia' => 'Benito Juárez', 'lat' => 19.3855, 'lng' => -99.1555, 'orden' => 480],
            ['nombre' => 'Hospital San José', 'tipo' => 'hospital', 'alcaldia' => 'Benito Juárez', 'lat' => 19.3955, 'lng' => -99.1655, 'orden' => 490],
            ['nombre' => 'Hospital Santelena', 'tipo' => 'hospital', 'alcaldia' => 'Benito Juárez', 'lat' => 19.3785, 'lng' => -99.1685, 'orden' => 500],
            ['nombre' => 'Hospital Torre Médica', 'tipo' => 'hospital', 'alcaldia' => 'Benito Juárez', 'lat' => 19.3825, 'lng' => -99.1725, 'orden' => 510],
            ['nombre' => 'Torre Médica Pedregal', 'tipo' => 'clinica', 'alcaldia' => 'Álvaro Obregón', 'lat' => 19.3155, 'lng' => -99.2185, 'orden' => 520],
            ['nombre' => 'Torre Médica Sur', 'tipo' => 'clinica', 'alcaldia' => 'Tlalpan', 'lat' => 19.2985, 'lng' => -99.1655, 'orden' => 530],
            ['nombre' => 'Torre Médica Polanco', 'tipo' => 'clinica', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4335, 'lng' => -99.1985, 'orden' => 540],
            ['nombre' => 'Torre Ángeles Pedregal', 'tipo' => 'clinica', 'alcaldia' => 'Álvaro Obregón', 'lat' => 19.3145, 'lng' => -99.2195, 'orden' => 550],
            ['nombre' => 'Torre Médica ABC Observatorio', 'tipo' => 'clinica', 'alcaldia' => 'Álvaro Obregón', 'lat' => 19.3988, 'lng' => -99.1962, 'orden' => 560],
            ['nombre' => 'Edificio Médico Santa Fe', 'tipo' => 'clinica', 'alcaldia' => 'Cuajimalpa', 'lat' => 19.3615, 'lng' => -99.2585, 'orden' => 570],
            ['nombre' => 'Plaza Médica Insurgentes', 'tipo' => 'plaza', 'alcaldia' => 'Benito Juárez', 'lat' => 19.3855, 'lng' => -99.1725, 'orden' => 580],
            ['nombre' => 'Plaza Inn', 'tipo' => 'plaza', 'alcaldia' => 'Benito Juárez', 'lat' => 19.3785, 'lng' => -99.1655, 'orden' => 590],
            ['nombre' => 'Plaza Universidad', 'tipo' => 'plaza', 'alcaldia' => 'Coyoacán', 'lat' => 19.3655, 'lng' => -99.1655, 'orden' => 600],
            ['nombre' => 'World Trade Center Ciudad de México', 'tipo' => 'plaza', 'alcaldia' => 'Benito Juárez', 'lat' => 19.3955, 'lng' => -99.1735, 'orden' => 610],
            ['nombre' => 'Centro Comercial Perisur', 'tipo' => 'plaza', 'alcaldia' => 'Coyoacán', 'lat' => 19.3055, 'lng' => -99.1855, 'orden' => 620],
            ['nombre' => 'Centro Comercial Santa Fe', 'tipo' => 'plaza', 'alcaldia' => 'Cuajimalpa', 'lat' => 19.3618, 'lng' => -99.2605, 'orden' => 630],
            ['nombre' => 'Centro Comercial Antara', 'tipo' => 'plaza', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4385, 'lng' => -99.2025, 'orden' => 640],
            ['nombre' => 'Paseo de la Reforma zona médica', 'tipo' => 'plaza', 'alcaldia' => 'Cuauhtémoc', 'lat' => 19.4285, 'lng' => -99.1655, 'orden' => 650],
            ['nombre' => 'Colonia Del Valle zona médica', 'tipo' => 'plaza', 'alcaldia' => 'Benito Juárez', 'lat' => 19.3855, 'lng' => -99.1685, 'orden' => 660],
            ['nombre' => 'Colonia Roma Norte zona médica', 'tipo' => 'plaza', 'alcaldia' => 'Cuauhtémoc', 'lat' => 19.4185, 'lng' => -99.1625, 'orden' => 670],
            ['nombre' => 'Colonia Condesa zona médica', 'tipo' => 'plaza', 'alcaldia' => 'Cuauhtémoc', 'lat' => 19.4125, 'lng' => -99.1725, 'orden' => 680],
            ['nombre' => 'Colonia Polanco zona médica', 'tipo' => 'plaza', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4325, 'lng' => -99.1958, 'orden' => 690],
            ['nombre' => 'Colonia Narvarte zona médica', 'tipo' => 'plaza', 'alcaldia' => 'Benito Juárez', 'lat' => 19.3955, 'lng' => -99.1555, 'orden' => 700],
            ['nombre' => 'Colonia San Ángel zona médica', 'tipo' => 'plaza', 'alcaldia' => 'Álvaro Obregón', 'lat' => 19.3455, 'lng' => -99.1885, 'orden' => 710],
            ['nombre' => 'Colonia Pedregal zona médica', 'tipo' => 'plaza', 'alcaldia' => 'Álvaro Obregón', 'lat' => 19.3155, 'lng' => -99.2185, 'orden' => 720],
            ['nombre' => 'UNAM Ciudad Universitaria zona médica', 'tipo' => 'universidad', 'alcaldia' => 'Coyoacán', 'lat' => 19.3325, 'lng' => -99.1858, 'orden' => 730],
            ['nombre' => 'Hospital General Regional No. 1 Carlos MacGregor', 'tipo' => 'hospital', 'alcaldia' => 'Benito Juárez', 'lat' => 19.3755, 'lng' => -99.1755, 'orden' => 740],
            ['nombre' => 'Hospital General Regional No. 2 Villa Coapa', 'tipo' => 'hospital', 'alcaldia' => 'Tlalpan', 'lat' => 19.2955, 'lng' => -99.1355, 'orden' => 750],
            ['nombre' => 'Hospital General Regional No. 25', 'tipo' => 'hospital', 'alcaldia' => 'Iztapalapa', 'lat' => 19.3555, 'lng' => -99.0555, 'orden' => 760],
            ['nombre' => 'Hospital General Regional No. 72', 'tipo' => 'hospital', 'alcaldia' => 'Tlalnepantla', 'ciudad' => 'Estado de México', 'estado' => 'Edomex', 'lat' => 19.5355, 'lng' => -99.1955, 'orden' => 770],
            ['nombre' => 'Hospital General de Zona 1-A Los Venados', 'tipo' => 'hospital', 'alcaldia' => 'Benito Juárez', 'lat' => 19.3655, 'lng' => -99.1555, 'orden' => 780],
            ['nombre' => 'Hospital General de Zona 8', 'tipo' => 'hospital', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4455, 'lng' => -99.2055, 'orden' => 790],
            ['nombre' => 'Hospital General de Zona 27', 'tipo' => 'hospital', 'alcaldia' => 'Venustiano Carranza', 'lat' => 19.4355, 'lng' => -99.1055, 'orden' => 800],
            ['nombre' => 'Hospital General de Zona 30', 'tipo' => 'hospital', 'alcaldia' => 'Iztapalapa', 'lat' => 19.3655, 'lng' => -99.0655, 'orden' => 810],
            ['nombre' => 'Hospital General de Zona 32', 'tipo' => 'hospital', 'alcaldia' => 'Iztapalapa', 'lat' => 19.3455, 'lng' => -99.0455, 'orden' => 820],
            ['nombre' => 'Hospital General de Zona 47', 'tipo' => 'hospital', 'alcaldia' => 'Gustavo A. Madero', 'lat' => 19.4955, 'lng' => -99.1055, 'orden' => 830],
            ['nombre' => 'ISSSTE Clínica Hospital Constitución', 'tipo' => 'hospital', 'alcaldia' => 'Venustiano Carranza', 'lat' => 19.4255, 'lng' => -99.1255, 'orden' => 840],
            ['nombre' => 'ISSSTE Clínica Hospital Dr. Fernando Quiroz Gutiérrez', 'tipo' => 'hospital', 'alcaldia' => 'Álvaro Obregón', 'lat' => 19.3755, 'lng' => -99.1955, 'orden' => 850],
            ['nombre' => 'ISSSTE Clínica Hospital Dr. Ignacio Chávez', 'tipo' => 'hospital', 'alcaldia' => 'Iztapalapa', 'lat' => 19.3555, 'lng' => -99.0755, 'orden' => 860],
            ['nombre' => 'Cruz Roja Mexicana Delegación Polanco', 'tipo' => 'clinica', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4355, 'lng' => -99.2055, 'orden' => 870],
            ['nombre' => 'Cruz Roja Mexicana Delegación Polanco Centro', 'tipo' => 'clinica', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4345, 'lng' => -99.2045, 'orden' => 871],
            ['nombre' => 'Hospital Juárez de la Mujer', 'tipo' => 'hospital', 'alcaldia' => 'Miguel Hidalgo', 'lat' => 19.4488, 'lng' => -99.1855, 'orden' => 880],
            ['nombre' => 'Hospital Materno Infantil Inguarán', 'tipo' => 'hospital', 'alcaldia' => 'Iztapalapa', 'lat' => 19.3655, 'lng' => -99.0855, 'orden' => 890],
            ['nombre' => 'Hospital Materno Infantil Magdalena Contreras', 'tipo' => 'hospital', 'alcaldia' => 'Magdalena Contreras', 'lat' => 19.3155, 'lng' => -99.2455, 'orden' => 900],
            ['nombre' => 'Hospital Pediátrico Azcapotzalco', 'tipo' => 'hospital', 'alcaldia' => 'Azcapotzalco', 'lat' => 19.4855, 'lng' => -99.1855, 'orden' => 910],
            ['nombre' => 'Hospital Pediátrico Iztapalapa', 'tipo' => 'hospital', 'alcaldia' => 'Iztapalapa', 'lat' => 19.3555, 'lng' => -99.0655, 'orden' => 920],
            ['nombre' => 'Hospital Pediátrico Moctezuma', 'tipo' => 'hospital', 'alcaldia' => 'Venustiano Carranza', 'lat' => 19.4255, 'lng' => -99.1055, 'orden' => 930],
            ['nombre' => 'Centro de Especialidades Médicas', 'tipo' => 'clinica', 'alcaldia' => 'Benito Juárez', 'lat' => 19.3885, 'lng' => -99.1685, 'orden' => 940],
            ['nombre' => 'Hospital Central Sur de Alta Especialidad Pemex', 'tipo' => 'hospital', 'alcaldia' => 'Tlalpan', 'lat' => 19.3088, 'lng' => -99.1858, 'orden' => 950],
            ['nombre' => 'Hospital Regional de Alta Especialidad de Ixtapaluca', 'tipo' => 'hospital', 'alcaldia' => 'Ixtapaluca', 'ciudad' => 'Estado de México', 'estado' => 'Edomex', 'lat' => 19.3155, 'lng' => -98.8855, 'orden' => 960],
            ['nombre' => 'Hospital General de Ecatepec Las Américas', 'tipo' => 'hospital', 'alcaldia' => 'Ecatepec', 'ciudad' => 'Estado de México', 'estado' => 'Edomex', 'lat' => 19.5555, 'lng' => -99.0455, 'orden' => 970],
            ['nombre' => 'Hospital General de Nezahualcóyotl', 'tipo' => 'hospital', 'alcaldia' => 'Nezahualcóyotl', 'ciudad' => 'Estado de México', 'estado' => 'Edomex', 'lat' => 19.4055, 'lng' => -99.0155, 'orden' => 980],
        ];
    }
}
