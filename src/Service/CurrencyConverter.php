<?php

declare(strict_types=1);

namespace RadevNRadoslav\CommissionTask\Service;

use GuzzleHttp\Client;

class CurrencyConverter
{
    private $rates;

    /**
     * Pull rates on obj initialization.
     *
     * @return void
     */
    public function __construct()
    {
        $this->pullRates();
    }

    /**
     * Get todays currency rates with EUR as a base rate.
     */
    protected function pullRates(): void
    {
        $client = new Client();
        $response = $client->request('GET', 'https://developers.paysera.com/tasks/api/currency-exchange-rates');

        $this->rates = json_decode($response->getBody()->getContents());
    }

    /**
     * Return rates in current object.
     */
    public function getRates(): object
    {
        return $this->rates;
    }

    /**
     * Convert a given amount from one currency to another.
     */
    public function convert(string $baseCurrency, string $desiredCurrency, float $amount): float
    {
        // Validate data consistency
        $baseCurrency = trim(strtoupper($baseCurrency));
        $desiredCurrency = trim(strtoupper($desiredCurrency));

        // If both currencies are the same no need for further execution
        if ($baseCurrency === $desiredCurrency) {
            return $amount;
        }

        $intermidiateConversion = $amount / $this->rates->rates->$baseCurrency;

        return $intermidiateConversion * $this->rates->rates->$desiredCurrency;
    }
}
