<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class QuestionPOSTApiTest extends TestCase
{
    private const APP_JSON = 'application/json';
    private string $baseUrl;
    private HttpClientInterface $client;
    protected function setUp(): void
    {
        parent::setUp();
        $this->baseUrl = $_ENV['TESTS_BASE_URL'] ?? 'https://questionaire.localhost';
        $this->client = HttpClient::create([
            'verify_peer' => false, // self-signed
            'verify_host' => false,
        ]);
    }

    private function createQuestionnaireId(): int
    {
        $response = $this->client->request('POST', $this->baseUrl . '/api/questionnaires', [
            'json' => [
                'title' => 'questionnaire for question',
                'description' => 'description',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(201, $response->getStatusCode());
        $data = json_decode($response->getContent(false), true, 512, JSON_THROW_ON_ERROR);
        return $data['data']['id'];
    }

    public function testCreateQuestionSuccess(): void
    {
        $postQuestionnaireId = $this->createQuestionnaireId();
        $postQuestion = $this->client->request('POST', $this->baseUrl . '/api/questions', [
            'json' => [
                'questionnaireId' => $postQuestionnaireId,
                'title' => 'My question',
                'description' => 'text',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(201, $postQuestion->getStatusCode());
        $data = $postQuestion->toArray(false);
        $this->assertArrayHasKey('data', $data);
        $question = $data['data'];

        $this->assertSame('My question', $question['title']);
        $this->assertSame('text', $question['description']);
        $this->assertSame($postQuestionnaireId, $question['questionnaireId']);
    }

    public function testCreateQuestionVide(): void
    {
        $postQuestion = $this->client->request('POST', $this->baseUrl . '/api/questions', [
            'json' => [
                'description' => 'text',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(405, $postQuestion->getStatusCode());
        $data = $postQuestion->toArray(false);
        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Invalid form', $data['error']);
    }

    public function testCreateQuestionInvalidQuestionnaireId(): void
    {
        $postQuestion = $this->client->request('POST', $this->baseUrl . '/api/questions', [
            'json' => [
                'questionnaireId' => 999999,
                'title' => 'My question',
                'description' => 'text',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(405, $postQuestion->getStatusCode());
        $data = $postQuestion->toArray(false);
        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Questionnaire not found', $data['error']);
    }
}
