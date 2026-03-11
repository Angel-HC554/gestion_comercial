<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Area;
use App\Models\Subarea;
use App\Models\AreaUsuario;
use Leaf\Helpers\Password;

class UserController extends Controller
{    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $areas = Area::all();

        render('users.index', [
            'areas' => $areas
        ]);
    }

    public function search()
    {
        $search = request()->get('search', '');
        $page = request()->get('page', 1);
        $perPage = 5;

        $query = User::query();

        if ($search) {
            $query->where('name', 'like', "%$search%")
            ->orWhere('user', 'like', "%$search%");
        }

        $total = $query->count();
        $lastPage = ceil($total / $perPage);

        if ($page < 1) $page = 1;
        if ($page > $lastPage && $lastPage > 0) $page = $lastPage;
        
        $users = $query->skip(($page -1) * $perPage)
        ->take($perPage)
        ->get();

        render('users.partials.filas', [
            'users' => $users,
            'page' => $page,
            'lastPage' => $lastPage,
            'search' => $search
        ]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function getSubareasOptions()
    {
        // HTMX envía el valor del select como parámetro GET con el nombre del input ('area_id')
        $areaId = request()->get('area_id');
        
        $subareas = Subarea::where('area_id', $areaId)->get();
        
        // Construimos el HTML de las opciones manualmente (es más rápido que crear una vista parcial)
        $html = '<option value="">Seleccione una ubicación...</option>';
        
        foreach ($subareas as $sub) {
            $html .= '<option value="' . $sub->id . '">' . $sub->nombre . '</option>';
        }
        
        // Retornamos el HTML directo
        return response()->markup($html);
    }

    public function addAssignment($id)
    {
        $data = request()->body();
        
        if (empty($data['area_id']) || empty($data['subarea_id'])) {
            return response()->markup("Error: Falta seleccionar área o ubicación", 400);
        }

        // Evitar duplicados
        $exists = AreaUsuario::where('user_id', $id)
                    ->where('area_id', $data['area_id'])
                    ->where('subarea_id', $data['subarea_id'])
                    ->exists();

        if (!$exists) {
            AreaUsuario::create([
                'user_id'    => $id,
                'area_id'    => $data['area_id'],
                'subarea_id' => $data['subarea_id']
            ]);
        }

        // Devolvemos la tabla actualizada
        return $this->renderAssignmentsTable($id);
    }

    public function removeAssignment($id, $assignmentId)
    {
        AreaUsuario::where('id', $assignmentId)->where('user_id', $id)->delete();
        return $this->renderAssignmentsTable($id);
    }

    private function renderAssignmentsTable($userId)
    {
        // Cargamos relaciones para mostrar nombres
        $assignments = AreaUsuario::with(['area', 'subarea'])
                        ->where('user_id', $userId)
                        ->get();

        // Renderizamos solo el pedacito de HTML de la tabla
        render('users.partials.assignments_table', [
            'assignments' => $assignments,
            'userId' => $userId
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        $data = request()->body();

        if (User::where('user', $data['user'])->exists()) {
            return response()->json(['message' => 'El identificador ya está registrado.'], 400);
        }

        $allowedMainRoles = ['admin', 'supervisor', 'oficinista'];
        $allowedExtraRoles = ['generar500'];

        $mainRole = $data['main_role'] ?? null;
        if (!$mainRole || !in_array($mainRole, $allowedMainRoles, true)) {
            return response()->json(['message' => 'El rol principal es inválido o falta.'], 400);
        }

        $roles = [$mainRole];
        $extraRole = $data['rol_extra'] ?? null;
        if (!empty($extraRole) && in_array($extraRole, $allowedExtraRoles, true)) {
            $roles[] = $extraRole;
        }

        try {
            db()->transaction(function () use ($data, $roles){
            $newUser = new User();
            $newUser->name = $data['name'];
            $newUser->user = $data['user'];
            $newUser->password = Password::hash($data['password']);
            $newUser->leaf_auth_user_roles = json_encode(array_values(array_unique($roles)));
            $newUser->save();

            // B. Asignar area y subarea
                $areas = $data['areas'] ?? [];
                $subareas = $data['subareas'] ?? [];
                $processedCombinations = [];

                if (!empty($areas) && is_array($areas)) {
                    foreach ($areas as $index => $areaId) {
                        $subareaId = $subareas[$index] ?? null;

                        if (!empty($areaId) && !empty($subareaId)) {
                            $comboKey = "{$areaId}-{$subareaId}";
                            if (!in_array($comboKey, $processedCombinations)) {
                                AreaUsuario::create([
                                    'user_id'    => $newUser->id,
                                    'area_id'    => $areaId,
                                    'subarea_id' => $subareaId
                                ]);
                                $processedCombinations[] = $comboKey;
                            }
                        }
                    }
                }
            });

            return response()->json(['message' => 'Usuario creado correctamente'], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::find($id);
        $roles = json_decode($user->leaf_auth_user_roles, true) ?? [];
        $areas = Area::all();
        $assignments = AreaUsuario::with('area', 'subarea')->where('user_id', $id)->get();
        return render('users.edit',[
            'user' => $user,
            'userRoles' => $roles,
            'areas' => $areas,
            'assignments' => $assignments
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        $user = User::findOrFail($id);
        $data = request()->body();
        
        try {
            // Update basic user info
            $user->name = $data['name'];
            $user->user = $data['user'];
            
            // Update password if provided
            if (!empty($data['password'])) {
                $user->password = Password::hash($data['password']);
            }
            
            $allowedMainRoles = ['admin', 'supervisor', 'oficinista'];
            $allowedExtraRoles = ['generar500'];

            $mainRole = $data['main_role'] ?? null;
            if (!$mainRole || !in_array($mainRole, $allowedMainRoles, true)) {
                return response()->json(['message' => 'El rol principal es inválido o falta.'], 400);
            }

            $roles = [$mainRole];
            $extraRole = $data['rol_extra'] ?? null;
            if (!empty($extraRole) && in_array($extraRole, $allowedExtraRoles, true)) {
                $roles[] = $extraRole;
            }

            $user->leaf_auth_user_roles = json_encode(array_values(array_unique($roles)));
            
            $user->save();
            
            return response()->json([
                'message' => 'Usuario actualizado correctamente',
                'user' => $user,
                'roles' => $roles
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el usuario: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        /*
        |--------------------------------------------------------------------------
        |
        | This is an example which deletes a particular row. 
        | You can un-comment it to use this example
        |
        */
        // $row = User::find($id);
        // $row->delete();
    }
}
