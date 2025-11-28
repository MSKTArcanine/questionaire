<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class QuestionDELETEApiTest extends TestCase
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

    public function testDeleteQuestionSuccess(): void{
        $questionnaireId = $this->createQuestionnaireId();

        $createQuestion = $this->client->request('POST', $this->baseUrl . '/api/questions', [
            'json' => [
                'questionnaireId' => $questionnaireId,
                'title' => 'Question to delete',
                'description' => 'Description',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(201, $createQuestion->getStatusCode());
        $created = $createQuestion->toArray(false);
        $id = $created['data']['id'];

        $deleteQuestion = $this->client->request('DELETE', $this->baseUrl . '/api/questions/' . $id);
        $this->assertSame(204, $deleteQuestion->getStatusCode());
        $data = $deleteQuestion->getContent(false);
        $this->assertSame('', $data);
    }

    public function testDeleteQuestionNotFound(): void{
        $deleteQuestion = $this->client->request('DELETE', $this->baseUrl . '/api/questions/999999');
        $this->assertSame(404, $deleteQuestion->getStatusCode());

        $data = $deleteQuestion->toArray(false);
        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Question not found', $data['error']);
    }
}
