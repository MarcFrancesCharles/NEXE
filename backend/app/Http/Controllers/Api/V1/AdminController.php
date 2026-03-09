<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Usuari;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Llistar tots els usuaris
    public function llistarUsuaris()
    {
        return response()->json(Usuari::with('perfil')->get());
    }

    // Bloquejar o desbloquejar un usuari
    public function canviarEstat(Request $request, $id)
    {
        $usuari = Usuari::findOrFail($id);
        
        // Si està actiu el bloquegem, si està bloquejat l'activem
        $usuari->estat = ($usuari->estat === 'ACTIU') ? 'BLOQUEJAT' : 'ACTIU';
        $usuari->save();

        return response()->json([
            'missatge' => "L'estat de l'usuari ara és: " . $usuari->estat,
            'usuari' => $usuari
        ]);
    }
}