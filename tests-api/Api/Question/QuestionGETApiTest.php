<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

class QuestionGETApiTest extends TestCase
{
    private string $baseUrl;
    protected function setUp(): void
    {
        parent::setUp();
        $this->baseUrl = $_ENV['TESTS_BASE_URL'] ?? 'https://questionaire.localhost';
    }

    public function testListQuestions(): void
    {
        $client = HttpClient::create([
            'verify_peer' => false,
            'verify_host' => false,
        ]);

        $response = $client->request('GET', $this->baseUrl . '/api/questions');

        $this->assertSame(200, $response->getStatusCode());

        $data = $response->toArray();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
        $this->assertNotEmpty($data['data']);
    }

    public function testQuestionNotFound(): void
    {
        $client = HttpClient::create([
            'verify_peer' => false,
            'verify_host' => false,
        ]);

        $response = $client->request('GET', $this->baseUrl . '/api/questions/999999');

        $this->assertSame(404, $response->getStatusCode());

        $data = $response->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Question not found', $data['error']);
    }

    public function testQuestionBase(): void
    {
        $client = HttpClient::create([
            'verify_peer' => false,
            'verify_host' => false,
        ]);

        $response = $client->request('GET', $this->baseUrl . '/api/questions/1');

        $this->assertSame(200, $response->getStatusCode());

        $data = $response->toArray();

        $this->assertArrayHasKey('data', $data);
        $question = $data['data'];

        $this->assertSame(1, $question['id']);
        $this->assertArrayHasKey('title', $question);
        $this->assertArrayHasKey('description', $question);
        $this->assertArrayHasKey('questionnaireId', $question);
    }
}
