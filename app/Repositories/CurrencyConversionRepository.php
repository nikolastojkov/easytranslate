<?php

namespace App\Repositories;

use App\Contracts\CurrencyConversionContract;
use App\Exceptions\DatabaseOperationException;
use App\Models\CurrencyConversion;
use DB;
use Illuminate\Database\QueryException;

final readonly class CurrencyConversionRepository implements CurrencyConversionContract
{
    public function persistCurrencyConversion(
        string $sourceCurrency,
        string $targetCurrency,
        string $value,
        float $convertedValue,
        float $rate
    ): int {
        try {
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
        } catch (QueryException $e) {
            throw new DatabaseOperationException(message: "Failed to persist currency conversion: {$e->getMessage()}");
        } catch (\Exception $e) {
            throw new DatabaseOperationException(message: "Unexpected database error: {$e->getMessage()}");
        }
    }
}