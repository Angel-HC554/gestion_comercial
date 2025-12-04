<?php

namespace App\Controllers;

use App\Models\Vehiculo;
use App\Models\SupervisionDiaria;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        Carbon::setLocale('es');
        $hoy = Carbon::today();

        // 1. KPIs Generales
        $totalVehiculos = Vehiculo::count();
        $totalSupervisionesHoy = SupervisionDiaria::whereDate('fecha', $hoy)->count();
        
        // Calcular porcentaje de avance diario (evitando división por cero)
        $avanceDiario = $totalVehiculos > 0 ? round(($totalSupervisionesHoy / $totalVehiculos) * 100) : 0;

        // Vehículos con reporte de golpes HOY
        $vehiculosConGolpes = SupervisionDiaria::whereDate('fecha', $hoy)
                                ->where('golpes', true)
                                ->count();

        // 2. Datos para Gráfica: Cumplimiento últimos 7 días
        $graficaDias = [];
        $graficaValores = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::today()->subDays($i);
            $graficaDias[] = $fecha->format('d M'); // Ej: "30 Nov"
            $graficaValores[] = SupervisionDiaria::whereDate('fecha', $fecha)->count();
        }

        // 3. Datos para Gráfica: Nivel de Gasolina (De los reportes de hoy)
        // Agrupamos por nivel de gasolina (0, 25, 50, 75, 100)
        $gasolinaStats = SupervisionDiaria::whereDate('fecha', $hoy)
            ->selectRaw('gasolina, count(*) as total')
            ->groupBy('gasolina')
            ->orderBy('gasolina')
            ->pluck('total', 'gasolina')->toArray();
            
        // Formatear para ApexCharts (asegurar que existan todos los keys)
        $niveles = ['0', '25', '50', '75', '100'];
        $gasolinaData = [];
        foreach ($niveles as $nivel) {
            $gasolinaData[] = $gasolinaStats[$nivel] ?? 0;
        }

        render('dashboard.diario', [
            'totalVehiculos' => $totalVehiculos,
            'avanceDiario' => $avanceDiario,
            'supervisionesHoy' => $totalSupervisionesHoy,
            'vehiculosConGolpes' => $vehiculosConGolpes,
            'graficaDias' => json_encode($graficaDias),
            'graficaValores' => json_encode($graficaValores),
            'gasolinaData' => json_encode($gasolinaData)
        ]);
    }
}