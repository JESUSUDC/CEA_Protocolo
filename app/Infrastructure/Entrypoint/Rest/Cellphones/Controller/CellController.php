<?php
namespace App\Infrastructure\Entrypoint\Rest\Cellphones\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class CellController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['message' => 'Hola Mundo Cellphones'], 200);
    }
}