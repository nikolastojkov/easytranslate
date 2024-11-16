<?php
namespace App\Services;

use App\Contracts\CurrencyConversionContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final readonly class CurrencyConversionService
{
    public function __construct(protected CurrencyConversionContract $repository)
    {
    }

    public function convertCurrency($sourceCurrency, $targetCurrency, $value): JsonResponse
    {
        $rate = $this->getRate(
            sourceCurrency: $sourceCurrency,
            targetCurrency: $targetCurrency
        );

        if (!$rate) {
            return response()->json(
                data: ['error' => 'Unable to fetch exchange rate.'],
                status: 500
            );
        }

        $convertedValue = $value * $rate;
        $conversionRequestId = $this->repository->persistCurrencyConversion(
            sourceCurrency: $sourceCurrency,
            targetCurrency: $targetCurrency,
            value: $value,
            convertedValue: $convertedValue,
            rate: $rate
        );

        return response()->json(data: [
            'success' => true,
            'data' => [
                'source_currency' => $sourceCurrency,
                'target_currency' => $targetCurrency,
                'value' => $value,
                'converted_value' => $convertedValue,
                'rate' => $rate,
            ],
            'conversion_request_id' => $conversionRequestId,
        ]);
    }

    private function getRate($sourceCurrency, $targetCurrency): mixed
    {
        $cacheKey = "rate_{$sourceCurrency}_{$targetCurrency}";
        return Cache::remember(
            key: $cacheKey,
            ttl: now()->addSeconds(value: 10),
            callback: function () use ($sourceCurrency, $targetCurrency): mixed {
                return $this->fetchExchangeRate(
                    sourceCurrency: $sourceCurrency,
                    targetCurrency: $targetCurrency
                );
            }
        );
    }

    private function fetchExchangeRate($sourceCurrency, $targetCurrency): mixed
    {
        $apiKey = env(key: 'FIXER_API_KEY');
        $response = Http::get(
            url: "http://data.fixer.io/api/latest",
            query: [
                'access_key' => $apiKey,
                'base' => $sourceCurrency,
                'symbols' => $targetCurrency,
            ]
        );

        if ($response->successful() && isset($response['rates'][$targetCurrency])) {
            $rate = $response['rates'][$targetCurrency];

            return $rate;
        }

        return null;
    }
}
