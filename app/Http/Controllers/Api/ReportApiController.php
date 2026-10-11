<?php

namespace App\Http\Controllers\Api;

use App\Actions\SaveReport;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReportApiController extends Controller
{
    /**
     * Dépôt d'un rapport. Un seul rapport par date : renvoyer la même date
     * remplace l'existant (201 création, 200 mise à jour, 400 invalide).
     */
    public function store(Request $request, SaveReport $save): JsonResponse
    {
        try {
            $result = $save($request->only(['date', 'title', 'summary', 'content_md']));
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->first()], 400);
        }

        return response()->json(['ok' => true, ...$result], $result['created'] ? 201 : 200);
    }
}
