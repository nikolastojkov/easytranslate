<?php

namespace App\Http\Controllers;

use App\Exceptions\CurrencyConversionException;
use App\Exceptions\InvalidCurrencyException;
use App\Http\Requests\CurrencyConversionRequest;
use App\Services\CurrencyConversionService;
use Illuminate\Http\JsonResponse;

final class ConvertCurrency extends Controller
{
    public function __construct(private CurrencyConversionService $service)
    {
    }

    public function __invoke(CurrencyConversionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $responseObject = $this->service->convertCurrency(
                sourceCurrency: strtoupper(string: $validated['source_currency']),
                targetCurrency: strtoupper(string: $validated['target_currency']),
                value: $validated['value']
            );

            if (isset($validated['state'])) {
                $data = $responseObject->getData(assoc: true);
                $data['state'] = $validated['state'];
                $responseObject->setData(data: $data);
            }

            return $responseObject;
        } catch (InvalidCurrencyException $e) {
            return response()->json(data: [
                'error' => $e->getMessage(),
            ], status: 400);
        } catch (CurrencyConversionException $e) {
            return response()->json(data: [
                'error' => $e->getMessage(),
            ], status: 500);
        } catch (\Exception $e) {
            // Fallback
            return response()->json(data: [
                'error' => 'An unexpected error occurred.',
            ], status: 500);
        }

    }

}
