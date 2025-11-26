<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

class ChoiceGETApiTest extends TestCase
{
    private string $baseUrl;
    protected function setUp(): void
    {
        parent::setUp();
        $this->baseUrl = $_ENV['TESTS_BASE_URL'] ?? 'https://questionaire.localhost';
    }

    public function testListChoices(): void
    {
        $client = HttpClient::create([
            'verify_peer' => false,
            'verify_host' => false,
        ]);

        $response = $client->request('GET', $this->baseUrl . '/api/choices');

        $this->assertSame(200, $response->getStatusCode());

        $data = $response->toArray();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
        $this->assertNotEmpty($data['data']);
    }

    public function testChoiceNotFound(): void
    {
        $client = HttpClient::create([
            'verify_peer' => false,
            'verify_host' => false,
        ]);

        $response = $client->request('GET', $this->baseUrl . '/api/choices/999999');

        $this->assertSame(404, $response->getStatusCode());

        $data = $response->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Choice not found', $data['error']);
    }

    public function testChoiceBase(): void
    {
        $client = HttpClient::create([
            'verify_peer' => false,
            'verify_host' => false,
        ]);

        $response = $client->request('GET', $this->baseUrl . '/api/choices/1');

        $this->assertSame(200, $response->getStatusCode());

        $data = $response->toArray();

        $this->assertArrayHasKey('data', $data);
        $choice = $data['data'];

        $this->assertSame(1, $choice['id']);
        $this->assertArrayHasKey('content', $choice);
        $this->assertArrayHasKey('questionId', $choice);
        $this->assertArrayHasKey('nextQuestionId', $choice);
    }
}
