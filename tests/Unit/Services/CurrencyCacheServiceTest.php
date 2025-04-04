<?php

namespace App\Tests\Unit\Services;

use App\Service\CurrencyConversion\CurrencyCacheService;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CurrencyCacheServiceTest extends TestCase
{
    public function testGetAllRatesReturnsDataWhenCacheHit(): void
    {
        $key = 'fastforex_rates_usd';
        $expectedRates = ['EUR' => 0.85];

        $item = $this->createMock(CacheItemInterface::class);
        $item->method('isHit')->willReturn(true);
        $item->method('get')->willReturn($expectedRates);

        $pool = $this->createMock(CacheItemPoolInterface::class);
        $pool->method('getItem')->with($key)->willReturn($item);

        $service = new CurrencyCacheService($pool);

        $result = $service->getAllRates('USD');
        $this->assertSame($expectedRates, $result);
    }

    public function testGetAllRatesReturnsNullWhenCacheMissing(): void
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->method('isHit')->willReturn(false);

        $pool = $this->createMock(CacheItemPoolInterface::class);
        $pool->method('getItem')->willReturn($item);

        $service = new CurrencyCacheService($pool);

        $result = $service->getAllRates('USD');
        $this->assertNull($result);
    }

    public function testSetAllRatesStoresValueAndSetsExpiration(): void
    {
        $rates = ['EUR' => 0.85];
        $key = 'fastforex_rates_usd';

        $item = $this->createMock(CacheItemInterface::class);
        $item->expects($this->once())->method('set')->with($rates);
        $item->expects($this->once())->method('expiresAfter')->with(432000);

        $pool = $this->createMock(CacheItemPoolInterface::class);
        $pool->method('getItem')->with($key)->willReturn($item);
        $pool->expects($this->once())->method('save')->with($item);

        $service = new CurrencyCacheService($pool);
        $service->setAllRates('USD', $rates);
    }
}
