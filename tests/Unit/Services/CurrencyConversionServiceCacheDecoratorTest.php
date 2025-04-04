<?php
namespace App\Tests\Unit\Services;

use App\Interfaces\CurrencyConversionServiceInterface;
use App\Service\CurrencyConversion\CurrencyCacheService;
use App\Service\CurrencyConversion\CurrencyConversionServiceCacheDecorator;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;

class CurrencyConversionServiceCacheDecoratorTest extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testCacheIsBeingSetWhenExternalApiSucceeds(): void
    {
        $decorated = $this->createMock(CurrencyConversionServiceInterface::class);
        $decorated->expects($this->once())
            ->method('getExchangeRate')
            ->with('USD', 'EUR')
            ->willReturn(0.85);

        $cache = $this->createMock(CurrencyCacheService::class);
        $cache->expects($this->once())
            ->method('setAllRates')
            ->with('USD', ['EUR' => 0.85]);

        $decorator = new CurrencyConversionServiceCacheDecorator($decorated, $cache);

        $rate = $decorator->getExchangeRate('USD', 'EUR');
        $this->assertSame(0.85, $rate);
    }
}
