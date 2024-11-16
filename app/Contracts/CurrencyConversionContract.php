<?php
namespace App\Contracts;

interface CurrencyConversionContract
{
    public function persistCurrencyConversion(
        string $sourceCurrency,
        string $targetCurrency,
        string $value,
        float $convertedValue,
        float $rate
    ): int;
}