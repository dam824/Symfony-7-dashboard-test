<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

class WordPressService
{
    private $client;
    private $logger;
    private $wordpressUrl = 'http://localhost:8888/Symfony/symfony-wp/wp-json/wp/v2';
    private $jwtToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0Ojg4ODgvU3ltZm9ueS9zeW1mb255LXdwIiwiaWF0IjoxNzE3MDE0OTcyLCJuYmYiOjE3MTcwMTQ5NzIsImV4cCI6MTcxNzYxOTc3MiwiZGF0YSI6eyJ1c2VyIjp7ImlkIjoiMSJ9fX0.o36KdNEpdcsZRu6iBgebtrMIaZI0axegv8whvhJm67Y';

    public function __construct(HttpClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function createPost(array $data): JsonResponse
    {
        $this->logger->info('Sending request to create post.', ['data' => $data]);
        $response = $this->client->request('POST', $this->wordpressUrl . '/posts', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->jwtToken,
                'Content-Type' => 'application/json'
            ],
            'json' => $data,
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        $this->logger->info('Response from WordPress.', ['statusCode' => $statusCode, 'content' => $content]);

        return new JsonResponse(json_decode($content, true), $statusCode);
    }

    public function uploadImage(string $filePath): int
    {
        $this->logger->info('Sending request to upload image.', ['filePath' => $filePath]);
        $response = $this->client->request('POST', $this->wordpressUrl . '/media', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->jwtToken,
                'Content-Disposition' => 'attachment; filename="' . basename($filePath) . '"',
                'Content-Type' => mime_content_type($filePath),
            ],
            'body' => file_get_contents($filePath),
        ]);

        $data = $response->toArray();
        $this->logger->info('Response from WordPress on image upload.', ['data' => $data]);

        return $data['id'];
    }
}
