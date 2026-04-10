<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Comerc;

class UpdateComercRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Validat pel middleware a les rutes
    }

    public function rules(): array
    {
        // Busquem el comerç de l'usuari per saber quin ID té (i així poder ignorar-lo al CIF)
        $userId = $this->user()->id_usuari;
        $comerc = Comerc::where('id_usuari', $userId)->first();
        
        if (!$comerc && $this->user()->rol === 'ADMIN') {
            $comerc = Comerc::first();
        }

        $comercId = $comerc ? $comerc->id_comerc : null;

        return [
            // --- Camps bàsics ---
            'nom_comercial' => 'sometimes|required|string|max:100',
            'id_categoria'  => 'sometimes|required|exists:categorias,id_categoria',
            'cif' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('comercs', 'cif')->ignore($comercId, 'id_comerc')
            ],
            'imatge' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',

            // --- Nous camps de contacte i descripció ---
            'descripcio'      => 'nullable|string|max:2000',
            'telefon'         => 'nullable|string|max:20',
            'email_contacte'  => 'nullable|email|max:100',
            'enllac_web'      => 'nullable|url|max:255',
            'instagram'       => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'email_contacte.email' => 'El correu de contacte no té un format vàlid.',
            'enllac_web.url'       => 'L\'enllaç web ha de ser una URL vàlida (ex: https://...).',
        ];
    }
}