<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CurrencyConversionController;

Route::post(
    uri: 'v1/convert-currency',
    action: [CurrencyConversionController::class, 'convert']
);
