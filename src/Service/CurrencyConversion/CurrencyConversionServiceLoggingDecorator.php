<?php

namespace App\Service\CurrencyConversion;

use App\Exceptions\CurrencyConversionException;
use App\Interfaces\CurrencyConversionServiceInterface;
use Psr\Log\LoggerInterface;

readonly class CurrencyConversionServiceLoggingDecorator implements CurrencyConversionServiceInterface
{
    public function __construct(
        private CurrencyConversionServiceInterface $decorated,
        private CurrencyCacheService               $cache,
        private LoggerInterface                    $logger
    ) {}

    public function getExchangeRate(string $fromCurrency, string $toCurrency): float
    {
        $from = strtoupper($fromCurrency);
        $to = strtoupper($toCurrency);

        try {
            return $this->decorated->getExchangeRate($from, $to);
        } catch (\Throwable $e) {
            $this->logPrimaryFailure($e, $from, $to);

            return $this->getFromCacheOrFail($from, $to);
        }
    }

    private function logPrimaryFailure(\Throwable $e, string $from, string $to): void
    {
        $this->logger->error('Primary exchange rate provider failed, falling back to cache.', [
            'from' => $from,
            'to' => $to,
            'error' => $e->getMessage(),
        ]);
    }

    /**
     * @throws CurrencyConversionException
     */
    private function getFromCacheOrFail(string $from, string $to): float
    {
        try {
            $rates = $this->cache->getAllRates($from);
        } catch (\Throwable $ce) {
            $this->logger->error('Failed to fetch rates from cache', [
                'from' => $from,
                'to' => $to,
                'error' => $ce->getMessage(),
            ]);
            throw new CurrencyConversionException("Cash service failed.");
        }

        $rate = $rates[$to] ?? null;

        if ($rate === null) {
            $this->logger->error("Rate not found in cache for $from → $to");
            throw new CurrencyConversionException("Rate for $from → $to is currently unavailable in the local cache and the external service is not responding.");
        }

        return $rate;
    }

    public function convertAmount(string $amount, string $fromCurrency, string $toCurrency): string
    {
        $rate = $this->getExchangeRate($fromCurrency, $toCurrency);

        return number_format((float)$amount * $rate, 2, '.', '');
    }
}
