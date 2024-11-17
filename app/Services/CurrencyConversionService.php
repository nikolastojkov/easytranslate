<?php
namespace App\Services;

use App\Contracts\CurrencyConversionContract;
use App\Exceptions\CurrencyConversionException;
use App\Exceptions\InvalidCurrencyException;
use Illuminate\Http\Client\RequestException;
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
        try {
            $rate = retry(
                times: 3,
                callback: function () use ($sourceCurrency, $targetCurrency): mixed {
                    return $this->getRate(
                        sourceCurrency: $sourceCurrency,
                        targetCurrency: $targetCurrency
                    );
                },
                sleepMilliseconds: 100
            );

            if (!$rate) {
                throw new InvalidCurrencyException(message: "Invalid rate for conversion from {$sourceCurrency} to {$targetCurrency}");
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
        }  catch (InvalidCurrencyException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CurrencyConversionException(message: "Unexpected error during conversion: {$e->getMessage()}");
        }
    }

    private function getRate($sourceCurrency, $targetCurrency): mixed
    {
        try {
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
        } catch (\Exception $e) {
            throw new CurrencyConversionException(message: "Error while accessing cache for rate: {$sourceCurrency} to {$targetCurrency}");
        }
    }

    private function fetchExchangeRate($sourceCurrency, $targetCurrency): mixed
    {
        try {
            $apiKey = env(key: 'FIXER_API_KEY');
            $response = Http::timeout(seconds: 10)->get(
                url: "http://data.fixer.io/api/latest",
                query: [
                    'access_key' => $apiKey,
                    'base' => $sourceCurrency,
                    'symbols' => $targetCurrency,
                ]
            );

            if ($response->successful() && isset($response['rates'][$targetCurrency])) {
                return $response['rates'][$targetCurrency];
            }

            throw new InvalidCurrencyException(message: "Exchange rate not available for {$sourceCurrency} to {$targetCurrency}");
        } catch (RequestException $e) {
            throw new CurrencyConversionException(message: "API request failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            throw new CurrencyConversionException(message: "Unexpected error while fetching exchange rate.");
        }
    }
}
