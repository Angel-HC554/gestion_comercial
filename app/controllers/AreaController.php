<?php

namespace App\Controllers;

use App\Models\Area;
use App\Models\Subarea;

class AreaController extends Controller
{
    // 1. Vista Principal
    public function index()
    {
        $areas = Area::orderBy('nombre', 'asc')->get();
        render('areas.index', ['areas' => $areas]);
    }

    // --- LÓGICA DE ÁREAS (PADRE) ---

    public function storeArea()
    {
        $data = request()->body();
        if(empty($data['nombre'])) return response()->markup("Nombre requerido", 400);

        Area::create(['nombre' => strtoupper($data['nombre'])]);
        
        // Devolvemos la lista actualizada
        return $this->renderAreaList();
    }

    public function updateArea($id)
    {
        $data = request()->body();
        $area = Area::findOrFail($id);
        $area->update(['nombre' => strtoupper($data['nombre'])]);
        
        return $this->renderAreaList();
    }

    public function deleteArea($id)
    {
        // Al borrar el área, el 'ON DELETE CASCADE' de la BD borrará las subáreas automáticamente
        Area::destroy($id);
        return $this->renderAreaList();
    }

    // --- LÓGICA DE SUBÁREAS (HIJO) ---

    public function getSubareas($id)
    {
        $area = Area::findOrFail($id);
        $subareas = Subarea::where('area_id', $id)->orderBy('nombre', 'asc')->get();

        render('areas.partials.subareas_list', [
            'area' => $area,
            'subareas' => $subareas
        ]);
    }

    public function storeSubarea($id)
    {
        $data = request()->body();
        if(empty($data['nombre'])) return response()->markup("Nombre requerido", 400);

        Subarea::create([
            'area_id' => $id,
            'nombre' => strtoupper($data['nombre'])
        ]);

        // Reutilizamos la función getSubareas para devolver la lista actualizada
        return $this->getSubareas($id);
    }

    public function deleteSubarea($id)
    {
        $subarea = Subarea::findOrFail($id);
        $areaId = $subarea->area_id;
        $subarea->delete();

        return $this->getSubareas($areaId);
    }

    // Helper privado
    private function renderAreaList()
    {
        $areas = Area::orderBy('nombre', 'asc')->get();
        render('areas.partials.areas_list', ['areas' => $areas]);
    }
}