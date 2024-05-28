<?php 
 
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BitcoinService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getBitcoinHistoricalData(): array
    {
        $response = $this->client->request(
            'GET',
            'https://api.coingecko.com/api/v3/coins/bitcoin/market_chart?vs_currency=usd&days=30&interval=daily'
        );

        $data = $response->toArray();

        $historicalData = [];
        foreach ($data['prices'] as $priceData) {
            $date = date('Y-m-d', $priceData[0] / 1000);
            $price = $priceData[1];
            $historicalData[$date] = $price;
        }

        return $historicalData;
    }
}
