<?php

namespace Tests\Unit\Service;

use App\Service\ViaCepService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ViaCepServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_consulta_cep_returns_address_data(): void
    {
        $mockResponse = [
            'cep' => '01001-000',
            'logradouro' => 'Praça da Sé',
            'complemento' => 'lado ímpar',
            'bairro' => 'Sé',
            'localidade' => 'São Paulo',
            'uf' => 'SP',
            'ibge' => '3550308',
            'gia' => '1004',
            'ddd' => '11',
            'siafi' => '7107'
        ];

        Http::fake([
            'viacep.com.br/ws/01001000/json/' => Http::response($mockResponse)
        ]);

        $result = ViaCepService::consultaCEP('01001-000');

        $this->assertEquals($mockResponse, $result);
    }

    public function test_consulta_cep_uses_cache(): void
    {
        $mockResponse = ['cep' => '01001-000', 'logradouro' => 'Praça da Sé'];

        Http::fake([
            'viacep.com.br/ws/01001000/json/' => Http::response($mockResponse)
        ]);

        // First call should hit the API
        $firstResult = ViaCepService::consultaCEP('01001-000');

        // Change the mock response to verify cache is being used
        Http::fake([
            'viacep.com.br/ws/01001000/json/' => Http::response(['different' => 'response'])
        ]);

        // Second call should return cached result
        $secondResult = ViaCepService::consultaCEP('01001-000');

        $this->assertEquals($mockResponse, $firstResult);
        $this->assertEquals($firstResult, $secondResult);
    }

    public function test_consulta_cep_handles_api_error(): void
    {
        Http::fake([
            'viacep.com.br/ws/01001000/json/' => Http::response([], 500)
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Erro ao consultar CEP: Erro na consulta ao ViaCEP: 500');

        ViaCepService::consultaCEP('01001-000');
    }

    public function test_consulta_cep_handles_invalid_response(): void
    {
        Http::fake([
            'viacep.com.br/ws/01001000/json/' => Http::response(null)
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Erro ao consultar CEP: Resposta vazia do ViaCEP');

        ViaCepService::consultaCEP('01001-000');
    }

    public function test_consulta_cep_handles_not_found(): void
    {
        Http::fake([
            'viacep.com.br/ws/99999999/json/' => Http::response(['erro' => true])
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Erro ao consultar CEP: CEP não encontrado');

        ViaCepService::consultaCEP('99999-999');
    }
}