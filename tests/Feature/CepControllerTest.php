<?php

namespace Tests\Feature;

use App\Service\ViaCepService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CepControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_consulta_cep_returns_success_response(): void
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

        $response = $this->get('/api/consulta-cep/01001-000');

        $response->assertStatus(200)
                 ->assertJson($mockResponse);
    }

    public function test_consulta_cep_returns_404_for_non_existent_cep(): void
    {
        Http::fake([
            'viacep.com.br/ws/99999999/json/' => Http::response(['erro' => true])
        ]);

        $response = $this->get('/api/consulta-cep/99999-999');

        $response->assertStatus(404)
                 ->assertJson([
                     'error' => 'CEP não encontrado'
                 ]);
    }

    public function test_consulta_cep_returns_404_for_invalid_cep_format(): void
    {
        $response = $this->get('/api/consulta-cep/invalid-cep');

        $response->assertStatus(404);
    }

    public function test_consulta_cep_returns_500_for_api_error(): void
    {
        Http::fake([
            'viacep.com.br/ws/01001000/json/' => Http::response([], 500)
        ]);

        $response = $this->get('/api/consulta-cep/01001-000');

        $response->assertStatus(500)
                 ->assertJson([
                     'error' => 'Erro ao consultar CEP',
                     'message' => 'Erro ao consultar CEP: Erro na consulta ao ViaCEP: 500'
                 ]);
    }
}
