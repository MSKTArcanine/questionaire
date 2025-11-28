<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChoiceUPDATEApiTest extends TestCase
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

    private function createQuestionId(): int
    {
        $responseQuestionnaire = $this->client->request('POST', $this->baseUrl . '/api/questionnaires', [
            'json' => [
                'title' => 'questionnaire for question',
                'description' => 'description',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(201, $responseQuestionnaire->getStatusCode());
        $dataQuestionnaire = json_decode($responseQuestionnaire->getContent(false), true, 512, JSON_THROW_ON_ERROR);
        $questionnaireId = $dataQuestionnaire['data']['id'];

        $responseQuestion = $this->client->request('POST', $this->baseUrl . '/api/questions', [
            'json' => [
                'questionnaireId' => $questionnaireId,
                'title' => 'Question for choice',
                'description' => 'Description',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(201, $responseQuestion->getStatusCode());
        $dataQuestion = json_decode($responseQuestion->getContent(false), true, 512, JSON_THROW_ON_ERROR);
        return $dataQuestion['data']['id'];
    }

    public function testUpdateChoiceSuccess(): void
    {
        $questionId = $this->createQuestionId();

        $createChoice = $this->client->request('POST', $this->baseUrl . '/api/choices', [
            'json' => [
                'questionId' => $questionId,
                'content' => 'Original Content',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(201, $createChoice->getStatusCode());
        $created = $createChoice->toArray(false);
        $id = $created['data']['id'];

        $updateChoice = $this->client->request('PUT', $this->baseUrl . '/api/choices/' . $id, [
            'json' => [
                'content' => 'Updated Content',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(200, $updateChoice->getStatusCode());
        $data = $updateChoice->toArray(false);
        $this->assertArrayHasKey('data', $data);
        $choice = $data['data'];

        $this->assertSame('Updated Content', $choice['content']);
        $this->assertSame($questionId, $choice['questionId']);
    }

    public function testUpdateChoiceNotFound(): void
    {
        $updateChoice = $this->client->request('PUT', $this->baseUrl . '/api/choices/999999', [
            'json' => [
                'content' => 'osef',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(404, $updateChoice->getStatusCode());
        $data = $updateChoice->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Choice not found', $data['error']);
    }

    public function testUpdateChoiceInvalidQuestion(): void
    {
        $questionId = $this->createQuestionId();

        $createChoice = $this->client->request('POST', $this->baseUrl . '/api/choices', [
            'json' => [
                'questionId' => $questionId,
                'content' => 'Original Content',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(201, $createChoice->getStatusCode());
        $created = $createChoice->toArray(false);
        $id = $created['data']['id'];

        $updateChoice = $this->client->request('PUT', $this->baseUrl . '/api/choices/' . $id, [
            'json' => [
                'questionId' => 999999,
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(405, $updateChoice->getStatusCode());
        $data = $updateChoice->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Question not found', $data['error']);
    }
}
