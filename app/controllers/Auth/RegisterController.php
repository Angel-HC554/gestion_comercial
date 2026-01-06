<?php

namespace App\Controllers\Auth;

class RegisterController extends Controller
{
    public function show()
    {
        $form = flash()->display('form') ?? [];

        response()->view('pages.auth.register', array_merge((array) $form, [
            'errors' => flash()->display('error') ?? [],
        ]));
    }

    public function store()
    {
        $credentials = request()->validate([
            'name' => 'required|string',
            'user' => 'required|alpha_num',
            'password' => 'required|min:8',
            'confirmPassword*' => 'matchesValueOf:password',
        ]);

        if (!$credentials) {
            return response()
                ->withFlash('form', request()->body())
                ->withFlash('error', request()->errors())
                ->redirect('/auth/register');
        }

        $success = auth()->register($credentials);

        if (!$success) {
            return response()
                ->withFlash('form', request()->body())
                ->withFlash('error', auth()->errors())
                ->redirect('/auth/register');
        }

        return response()->redirect('/dashboard');
    }
}
