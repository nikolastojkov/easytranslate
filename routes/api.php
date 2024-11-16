<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConvertCurrency;

Route::post(
    uri: 'v1/convert-currency',
    action: ConvertCurrency::class
);
