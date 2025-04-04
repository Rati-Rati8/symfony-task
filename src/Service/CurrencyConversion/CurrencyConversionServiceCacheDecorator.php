<?php

namespace App\Service\CurrencyConversion;

use App\Interfaces\CurrencyConversionServiceInterface;
use Psr\Cache\InvalidArgumentException;

;

readonly class CurrencyConversionServiceCacheDecorator implements CurrencyConversionServiceInterface
{
    public function __construct(
        private CurrencyConversionServiceInterface $decorated,
        private CurrencyCacheService               $cache
    ) {}

    /**
     * @throws InvalidArgumentException
     */
    public function getExchangeRate(string $fromCurrency, string $toCurrency): float
    {
        $rate = $this->decorated->getExchangeRate($fromCurrency, $toCurrency);
        $this->cache->setAllRates(strtoupper($fromCurrency), [strtoupper($toCurrency) => $rate]);

        return $rate;
    }
}
