<?php
namespace App\Service\CurrencyConversion;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class CurrencyCacheService
{
    private const int TTL = 432000;

    public function __construct(private readonly CacheItemPoolInterface $cache) {}

    /**
     * @throws InvalidArgumentException
     */
    public function getAllRates(string $baseCurrency): ?array
    {
        $key = $this->getKey($baseCurrency);
        $item = $this->cache->getItem($key);

        return $item->isHit() ? $item->get() : null;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setAllRates(string $baseCurrency, array $rates): void
    {
        $key = $this->getKey($baseCurrency);
        $item = $this->cache->getItem($key);
        $item->set($rates);
        $item->expiresAfter(self::TTL);
        $this->cache->save($item);
    }

    private function getKey(string $baseCurrency): string
    {
        return 'fastforex_rates_' . strtolower($baseCurrency);
    }
}

