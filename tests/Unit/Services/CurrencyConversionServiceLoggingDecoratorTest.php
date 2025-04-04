<?php

namespace App\Tests\Unit\Services;

use App\Exceptions\CurrencyConversionException;
use App\Interfaces\CurrencyConversionServiceInterface;
use App\Service\CurrencyConversion\CurrencyCacheService;
use App\Service\CurrencyConversion\CurrencyConversionServiceLoggingDecorator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CurrencyConversionServiceLoggingDecoratorTest extends KernelTestCase
{
    public function testLogsErrorAndUsesCacheOnApiFailure(): void
    {
        $decorated = $this->createMock(CurrencyConversionServiceInterface::class);
        $decorated->method('getExchangeRate')->willThrowException(new \RuntimeException('API error'));

        $cache = $this->createMock(CurrencyCacheService::class);
        $cache->method('getAllRates')->with('USD')->willReturn(['EUR' => 0.85]);

        $logger = $this->createMock(LoggerInterface::class);

        $logger->expects($this->once())
            ->method('error');

        $decorator = new CurrencyConversionServiceLoggingDecorator($decorated, $cache, $logger);

        $rate = $decorator->getExchangeRate('USD', 'EUR');
        $this->assertSame(0.85, $rate);
    }

    public function testThrowsErrorWhenCacheIsNotWorking(): void
    {
        $decoded = $this->createMock(CurrencyConversionServiceInterface::class);
        $decoded->method('getExchangeRate')->willThrowException(new \RuntimeException('API error'));

        $cache = $this->createMock(CurrencyCacheService::class);
        $cache->method('getAllRates')->with('USD')
            ->willThrowException(new \Exception('Cash service crashed.'));

        $logger = $this->createMock(LoggerInterface::class);

        $decorator = new CurrencyConversionServiceLoggingDecorator($decoded, $cache, $logger);

        $this->expectException(CurrencyConversionException::class);
        $this->expectExceptionMessage('Cash service failed.');

        $decorator->getExchangeRate('USD', 'EUR');
    }

    public function testThrowsErrorWhenRateMissingInCache(): void
    {
        $decoded = $this->createMock(CurrencyConversionServiceInterface::class);
        $decoded->method('getExchangeRate')->willThrowException(new \RuntimeException('API error'));

        $cache = $this->createMock(CurrencyCacheService::class);
        $cache->method('getAllRates')->with('USD')->willReturn(['GBP' => 0.75]);

        $logger = $this->createMock(LoggerInterface::class);

        $decorator = new CurrencyConversionServiceLoggingDecorator($decoded, $cache, $logger);

        $this->expectException(CurrencyConversionException::class);
        $this->expectExceptionMessage('Rate for USD → EUR is currently unavailable in the local cache and the external service is not responding.');

        $decorator->getExchangeRate('USD', 'EUR');
    }
}
