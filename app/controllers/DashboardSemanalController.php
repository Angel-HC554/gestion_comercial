<?php

namespace App\Controllers;

use App\Models\Vehiculo;
use App\Models\SupervisionSemanal;
use Carbon\Carbon;

class DashboardSemanalController extends Controller
{
    public function index()
    {
        Carbon::setLocale('es');
        
        // --- 1. Definir la Semana Actual ---
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        
        $totalVehiculos = Vehiculo::count();

        // --- 2. KPI: Cumplimiento Semana Actual ---
        // Contamos supervisiones únicas por vehículo en este rango
        $supervisionesRealizadas = SupervisionSemanal::whereBetween('fecha_captura', [$inicioSemana, $finSemana])
                                    ->distinct('vehiculo_id')
                                    ->count('vehiculo_id');

        $faltantes = $totalVehiculos - $supervisionesRealizadas;
        $porcentajeCumplimiento = $totalVehiculos > 0 ? round(($supervisionesRealizadas / $totalVehiculos) * 100) : 0;

        // --- 3. Gráfica 1: Histórico últimas 8 Semanas ---
        $graficaSemanas = [];
        $graficaValores = [];

        for ($i = 7; $i >= 0; $i--) {
            $start = Carbon::now()->subWeeks($i)->startOfWeek();
            $end = Carbon::now()->subWeeks($i)->endOfWeek();
            
            // Etiqueta Eje X (Ej: "25 Nov - 01 Dic")
            $label = $start->format('d M') . ' - ' . $end->format('d M');
            $graficaSemanas[] = $label;

            // Valor Eje Y
            $count = SupervisionSemanal::whereBetween('fecha_captura', [$start, $end])
                        ->distinct('vehiculo_id')
                        ->count('vehiculo_id');
            $graficaValores[] = $count;
        }

        // --- 4. Gráfica 2: Cumplimiento por Agencia (Semana Actual) ---
        // Esto requiere un JOIN o cargar relaciones. Haremos la versión Eloquent simple.
        $supervisionesConVehiculo = SupervisionSemanal::with('vehiculo')
                                    ->whereBetween('fecha_captura', [$inicioSemana, $finSemana])
                                    ->get();

        $agenciaStats = [];
        foreach ($supervisionesConVehiculo as $sup) {
            $agencia = $sup->vehiculo->agencia ?? 'Sin Agencia';
            if (!isset($agenciaStats[$agencia])) {
                $agenciaStats[$agencia] = 0;
            }
            $agenciaStats[$agencia]++;
        }
        
        // Ordenar de mayor a menor y tomar top 5
        arsort($agenciaStats);
        $topAgenciasLabels = array_keys(array_slice($agenciaStats, 0, 5));
        $topAgenciasValues = array_values(array_slice($agenciaStats, 0, 5));

        render('dashboard.semanal', [
            'totalVehiculos' => $totalVehiculos,
            'realizadas' => $supervisionesRealizadas,
            'faltantes' => $faltantes,
            'porcentaje' => $porcentajeCumplimiento,
            'semanaLabel' => $inicioSemana->translatedFormat('d M') . ' al ' . $finSemana->translatedFormat('d M'),
            
            // Datos Gráficas
            'historiaLabels' => json_encode($graficaSemanas),
            'historiaValues' => json_encode($graficaValores),
            'agenciaLabels' => json_encode($topAgenciasLabels),
            'agenciaValues' => json_encode($topAgenciasValues),
        ]);
    }
}