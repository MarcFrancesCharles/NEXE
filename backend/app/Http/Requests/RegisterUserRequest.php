<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Qualsevol persona es pot registrar, no cal estar logat
    }

    public function rules(): array
    {
        $regles = [
            'nom' => 'required|string|max:100',
            'correu' => 'required|email|unique:usuaris,correu',
            'contrasenya' => 'required|min:8',
            'rol' => ['required', Rule::in(['ESTANDARD', 'COMERC', 'ADMIN'])],
        ];

        // Si el rol és COMERC, afegim les regles addicionals
        if ($this->input('rol') === 'COMERC') {
            $regles = array_merge($regles, [
                'id_categoria' => 'required|exists:categorias,id_categoria',
                'cif' => 'required|string|max:20',
                'latitud' => 'required|numeric|between:-90,90',
                'longitud' => 'required|numeric|between:-180,180',
            ]);
        }

        return $regles;
    }
}