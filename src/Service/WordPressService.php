<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class WordPressService
{
    private $client;
    private $wordpressUrl = 'http://localhost:8888/Symfony/symfony-wp/wp-json/wp/v2';
    private $jwtToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0Ojg4ODgvU3ltZm9ueS9zeW1mb255LXdwIiwiaWF0IjoxNzE2ODU2NTkzLCJuYmYiOjE3MTY4NTY1OTMsImV4cCI6MTcxNzQ2MTM5MywiZGF0YSI6eyJ1c2VyIjp7ImlkIjoiMSJ9fX0.FlFgbKxOWNqTG6qtrgFDUS3kWo4aV6itO0kU_v4m6nw'; // Remplacez par votre jeton JWT obtenu précédemment

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function createPost(array $data): JsonResponse
    {
        $response = $this->client->request('POST', $this->wordpressUrl . '/posts', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->jwtToken,
                'Content-Type' => 'application/json'
            ],
            'json' => $data,
        ]);

        return new JsonResponse($response->toArray(), $response->getStatusCode());
    }

    public function uploadImage(string $filePath): int
    {
        $response = $this->client->request('POST', $this->wordpressUrl . '/media', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->jwtToken,
                'Content-Disposition' => 'attachment; filename="' . basename($filePath) . '"',
                'Content-Type' => mime_content_type($filePath),
            ],
            'body' => file_get_contents($filePath),
        ]);

        $data = $response->toArray();
        return $data['id'];
    }
}
