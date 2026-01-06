<?php

namespace App\Controllers;

use App\Models\User;
use Leaf\Helpers\Password;

class UserController extends Controller
{    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        render('users.index');
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
            $newUser = new User();
            $newUser->name = $data['name'];
            $newUser->user = $data['user'];
            $newUser->password = Password::hash($data['password']);
            $newUser->leaf_auth_user_roles = json_encode(array_values(array_unique($roles)));
            $newUser->save();

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
        return render('users.edit',[
            'user' => $user
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
