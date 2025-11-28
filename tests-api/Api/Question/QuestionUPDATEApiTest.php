<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class QuestionUPDATEApiTest extends TestCase
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

    public function testUpdateQuestionSuccess(): void{
        $questionnaireId = $this->createQuestionnaireId();

        $createQuestion = $this->client->request('POST', $this->baseUrl . '/api/questions', [
            'json' => [
                'questionnaireId' => $questionnaireId,
                'title' => 'Original Title',
                'description' => 'Original Description',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(201, $createQuestion->getStatusCode());
        $created = $createQuestion->toArray(false);
        $id = $created['data']['id'];

        $updateQuestion = $this->client->request('PUT', $this->baseUrl . '/api/questions/' . $id, [
            'json' => [
                'title' => 'Updated Title',
                'description' => 'Updated Description',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(200, $updateQuestion->getStatusCode());
        $data = $updateQuestion->toArray(false);
        $question = $data['data'];

        $this->assertSame('Updated Title', $question['title']);
        $this->assertSame('Updated Description', $question['description']);
    }

    public function testUpdateQuestionNotFound(): void{
        $updateQuestion = $this->client->request('PUT', $this->baseUrl . '/api/questions/999999', [
            'json' => [
                'title' => 'osef',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(404, $updateQuestion->getStatusCode());
        $data = $updateQuestion->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Question not found', $data['error']);
    }

    public function testUpdateQuestionInvalid():void{
        $questionnaireId = $this->createQuestionnaireId();

        $createQuestion = $this->client->request('POST', $this->baseUrl . '/api/questions', [
            'json' => [
                'questionnaireId' => $questionnaireId,
                'title' => 'Original Title',
                'description' => 'Original Description',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(201, $createQuestion->getStatusCode());
        $created = $createQuestion->toArray(false);
        $id = $created['data']['id'];

        $updateQuestion = $this->client->request('PUT', $this->baseUrl . '/api/questions/' . $id, [
            'json' => [
                'questionnaireId' => 999999,
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(405, $updateQuestion->getStatusCode());
        $data = $updateQuestion->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Invalide questionnaire', $data['error']);
    }
}
