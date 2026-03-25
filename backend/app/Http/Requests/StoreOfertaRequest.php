<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfertaRequest extends FormRequest
{
    /**
     * Determina si l'usuari està autoritzat a fer aquesta petició.
     */
    public function authorize(): bool
    {
        // Com que ja validem el rol amb el Middleware (CheckRole:COMERC) a les rutes, 
        // aquí simplement retornem true.
        return true; 
    }

    /**
     * Obtenim les regles de validació.
     */
    public function rules(): array
    {
        return [
            'titol' => 'required|string|max:100',
            'cost_punts' => 'required|integer|min:1',
            'descripcio' => 'nullable|string', 
            'data_fi' => 'nullable|date',      
        ];
    }
}