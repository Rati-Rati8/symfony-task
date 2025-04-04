<?php

namespace App\Tests\Unit\Services;

use App\Exceptions\CurrencyConversionException;
use App\Service\Client\FastForexClient;
use App\Service\CurrencyConversion\CurrencyConversionServiceCore;
use PHPUnit\Framework\TestCase;

class CurrencyConversionServiceCoreTest extends TestCase
{
    public function testSameCurrencyReturnsOne(): void
    {
        $client = $this->createMock(FastForexClient::class);
        $client->expects($this->never())->method('fetchAllRates');

        $service = new CurrencyConversionServiceCore($client);

        $rate = $service->getExchangeRate('USD', 'USD');
        $this->assertSame(1.0, $rate);
    }

    public function testReturnsCorrectRateFromFastForex(): void
    {
        $client = $this->createMock(FastForexClient::class);
        $client->expects($this->once())
            ->method('fetchAllRates')
            ->with('USD')
            ->willReturn(['EUR' => 0.85]);

        $service = new CurrencyConversionServiceCore($client);

        $rate = $service->getExchangeRate('USD', 'EUR');
        $this->assertSame(0.85, $rate);
    }

    public function testThrowsErrorIfCurrencyNotFound(): void
    {
        $client = $this->createMock(FastForexClient::class);
        $client->method('fetchAllRates')->willReturn(['GBP' => 0.75]);

        $service = new CurrencyConversionServiceCore($client);

        $this->expectException(CurrencyConversionException::class);

        $service->getExchangeRate('USD', 'EUR');
    }
}
