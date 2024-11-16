<?php

namespace App\Repositories;

use App\Contracts\CurrencyConversionContract;
use App\Models\CurrencyConversion;

final readonly class CurrencyConversionRepository implements CurrencyConversionContract
{
    public function persistCurrencyConversion(
        string $sourceCurrency,
        string $targetCurrency,
        string $value,
        float $convertedValue,
        float $rate
    ): int {
        $model = CurrencyConversion::create(attributes: [
            'source_currency' => $sourceCurrency,
            'target_currency' => $targetCurrency,
            'value' => $value,
            'converted_value' => $convertedValue,
            'rate' => $rate,
        ]);

        return $model->id;
    }
}