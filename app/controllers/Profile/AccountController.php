<?php

namespace App\Controllers\Profile;
use Leaf\Helpers\Password;

class AccountController extends Controller
{
    public function show_update()
    {
        $user = auth()->user();
        $ultimoRespaldo = $this->obtenerUltimoRespaldo();

        response()->view('pages.profile.update', [
            'errors' => flash()->display('errors') ?? [],
            'flash' => [
                'success' => flash()->display('success')
            ],
            'name' => $user->name ?? null,
            'user' => $user->user ?? null,
            'ultimo_respaldo' => $ultimoRespaldo,
        ]);
    }

    public function update()
    {
        $data = request()->get(['user', 'name', 'password']);

        //validar contraseña
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Password::hash($data['password']);
        }
        
        // Validate usuario
        if (isset($data['user']) && !preg_match('/^[a-zA-Z0-9]+$/', $data['user'])) {
            return response()
                ->withFlash('errors', ['user' => 'El nombre de usuario solo puede contener letras y números'])
                ->redirect('/settings/profile');
        }
        
        // Validate nombre
        if (isset($data['name']) && !preg_match('/^[\p{L}\s]+$/u', $data['name'])) {
            return response()
                ->withFlash('errors', ['name' => 'El nombre solo puede contener letras y espacios'])
                ->redirect('/settings/profile');
        }

        if (!$data) {
            return response()
                ->withFlash('errors', request()->errors())
                ->redirect('/settings/profile');
        }

        $success = auth()->update($data);

        if (!$success) {
            return response()
                ->withFlash('errors', auth()->errors())
                ->redirect('/settings/profile');
        }

        response()
        ->withFlash('success', '¡Información actualizada correctamente!')
        ->redirect('/settings/profile');
    }
}
