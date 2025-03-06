<?php

use Illuminate\Support\Facades\Route;

Route::get('/consulta-cep/{cep}', [\App\Http\Controllers\CepController::class, 'consultaCep'])
    ->where('cep','^[0-9]{5}-?[0-9]{3}$');
