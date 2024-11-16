<?php

namespace App\Repositories;

use App\Contracts\CurrencyConversionContract;
use App\Models\CurrencyConversion;
use DB;

final readonly class CurrencyConversionRepository implements CurrencyConversionContract
{
    public function persistCurrencyConversion(
        string $sourceCurrency,
        string $targetCurrency,
        string $value,
        float $convertedValue,
        float $rate
    ): int {
        $entryId = 0;

        DB::transaction(callback: function () use ($sourceCurrency, $targetCurrency, $value, $convertedValue, $rate, &$entryId): mixed {
            $entryId = CurrencyConversion::create(attributes: [
                'source_currency' => $sourceCurrency,
                'target_currency' => $targetCurrency,
                'value' => $value,
                'converted_value' => $convertedValue,
                'rate' => $rate,
            ])->id;

            return $entryId;
        });

        return $entryId;
    }
}