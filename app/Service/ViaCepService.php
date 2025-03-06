<?php

namespace App\Service;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

class ViaCepService
{
    /**
     * Consulta um CEP no serviço ViaCEP
     *
     * @param string $cep
     * @return array
     * @throws Exception
     */
    public static function consultaCEP(string $cep): array
    {
        $cepNumbers = Str::numbers($cep);

        return Cache::remember(
            'viacep.' . $cepNumbers,
            \DateInterval::createFromDateString('1 day'),
            function () use ($cepNumbers) {
                try {
                    $response = Http::get('https://viacep.com.br/ws/' . $cepNumbers . '/json/');

                    if (!$response->successful()) {
                        throw new Exception('Erro na consulta ao ViaCEP: ' . $response->status());
                    }

                    $data = $response->json();

                    if (empty($data)) {
                        throw new Exception('Resposta vazia do ViaCEP');
                    }

                    if (isset($data['erro']) && $data['erro'] === true) {
                        throw new Exception('CEP não encontrado');
                    }

                    return $data;
                } catch (Exception $e) {
                    throw new Exception('Erro ao consultar CEP: ' . $e->getMessage());
                }
            }
        );
    }
}
