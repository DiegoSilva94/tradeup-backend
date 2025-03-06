<?php
namespace App\Http\Controllers;

use App\Http\Requests\ConsultaCepRequest;
use App\Service\ViaCepService;
use Illuminate\Http\JsonResponse;

class CepController extends Controller
{
    public function consultaCep(string $cep): JsonResponse
    {
        try {
            $result = ViaCepService::consultaCEP($cep);
            return response()->json($result);
        } catch (\Exception $e) {
            $statusCode = 500;
            $errorMessage = 'Erro ao consultar CEP';

            if (str_contains($e->getMessage(), 'CEP não encontrado')) {
                $statusCode = 404;
                $errorMessage = 'CEP não encontrado';
            }

            return response()->json([
                'error' => $errorMessage,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }
}
