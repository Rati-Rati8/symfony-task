<?php

namespace App\Service\Client;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FastForexClient
{
    private const string BASE_URL = 'https://api.fastforex.io/fetch-all';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey
    ) {}

    /**
     * Fetches all rates for a given base currency.
     *
     * @param string $baseCurrency
     * @return array<string, float>
     * @throws TransportExceptionInterface
     */
    public function fetchAllRates(string $baseCurrency): array
    {
        $response = $this->httpClient->request('GET', self::BASE_URL, [
            'query' => [
                'api_key' => $this->apiKey,
                'from' => strtoupper($baseCurrency),
            ]
        ]);

        $data = $response->toArray();

        if (!isset($data['results']) || !is_array($data['results'])) {
            throw new \RuntimeException('Unexpected FastForex API response.');
        }

        return $data['results'];
    }
}
