<?php

namespace App\Controllers\Profile;

class AccountController extends Controller
{
    public function show_update()
    {
        $user = auth()->user();

        response()->view('pages.profile.update', [
            'errors' => flash()->display('errors') ?? [],
            'name' => $user->name ?? null,
            'user' => $user->user ?? null,
        ]);
    }

    public function update()
    {
        $data = request()->get(['user', 'name']);
        
        // Validate user (alphanumeric only, optional)
        if (isset($data['user']) && !preg_match('/^[a-zA-Z0-9]+$/', $data['user'])) {
            return response()
                ->withFlash('errors', ['user' => 'El nombre de usuario solo puede contener letras y números'])
                ->redirect('/settings/profile');
        }
        
        // Validate name (text, optional)
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

        response()->redirect('/dashboard');
    }
}
