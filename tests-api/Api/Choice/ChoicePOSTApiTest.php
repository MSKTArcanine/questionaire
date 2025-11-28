<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChoicePOSTApiTest extends TestCase
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

    public function testCreateChoiceSuccess(): void
    {
        $questionId = $this->createQuestionId();
        $postChoice = $this->client->request('POST', $this->baseUrl . '/api/choices', [
            'json' => [
                'questionId' => $questionId,
                'content' => 'test',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(201, $postChoice->getStatusCode());
        $data = $postChoice->toArray(false);
        $this->assertArrayHasKey('data', $data);
        $choice = $data['data'];

        $this->assertSame('test', $choice['content']);
        $this->assertSame($questionId, $choice['questionId']);
        $this->assertArrayHasKey('nextQuestionId', $choice);
    }

    public function textCreateChoiceInvalid():void{
        $postChoice = $this->client->request('POST', $this->baseUrl . '/api/choices', [
            'json' => [
                'content' => 'test fail',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(405, $postChoice->getStatusCode());
        $data = $postChoice->toArray(false);
        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Invalid form', $data['error']);
    }

    public function testCreateChoiceInvalidQuestionId(): void
    {
        $postChoice = $this->client->request('POST', $this->baseUrl . '/api/choices', [
            'json' => [
                'questionId' => 999999,
                'content' => 'test invalid question',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(405, $postChoice->getStatusCode());
        $data = $postChoice->toArray(false);
        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Question not found', $data['error']);
    }
}
