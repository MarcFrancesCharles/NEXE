<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    /**
     * Retorna totes les categories principals amb les seves subcategories.
     */
    public function index(): JsonResponse
    {
        // whereNull('parent_id') filtra per obtenir només les categories pare
        // with('subcategories') fa "eager loading" per incloure-hi els fills
        $categories = Categoria::with('subcategories')
            ->whereNull('parent_id')
            ->get();

        return response()->json($categories);
    }
}