<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    // Aquesta funció intercepta la petició abans d'arribar al controlador
    public function handle(Request $request, Closure $next, string $rol): Response
    {
        // L'ADMIN sempre té permís per a tot ("hace de todo")
        if ($request->user() && $request->user()->rol === 'ADMIN') {
            return $next($request);
        }

        // Si l'usuari no està logat o el seu rol no coincideix amb el necessari, el bloquegem
        if (!$request->user() || $request->user()->rol !== $rol) {
            return response()->json([
                'missatge' => 'Accés denegat. Aquesta acció és només per a usuaris amb rol: ' . $rol
            ], 403);
        }

        // Si té el rol correcte, el deixem passar
        return $next($request);
    }
}