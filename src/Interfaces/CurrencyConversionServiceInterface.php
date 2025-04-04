<?php

namespace App\Interfaces;

interface CurrencyConversionServiceInterface
{
    public function getExchangeRate(string $fromCurrency, string $toCurrency): float;
}
