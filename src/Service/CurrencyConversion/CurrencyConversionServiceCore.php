<?php
namespace App\Service\CurrencyConversion;

use App\Exceptions\CurrencyConversionException;
use App\Interfaces\CurrencyConversionServiceInterface;
use App\Service\Client\FastForexClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

readonly class CurrencyConversionServiceCore implements CurrencyConversionServiceInterface
{
    public function __construct(private FastForexClient $fastForexClient) {}

    /**
     * @throws TransportExceptionInterface|CurrencyConversionException
     */
    public function getExchangeRate(string $fromCurrency, string $toCurrency): float
    {
        if (strtoupper($fromCurrency) === strtoupper($toCurrency)) {
            return 1.0;
        }

        $rates = $this->fastForexClient->fetchAllRates(strtoupper($fromCurrency));

        return $rates[strtoupper($toCurrency)]
            ?? throw new CurrencyConversionException("Exchange rate not found.");
    }
}
