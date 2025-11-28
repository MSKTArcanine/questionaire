<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class QuestionnairePOSTApiTest extends TestCase
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

    public function testCreateQuestionnaires(): void
    {
        $response = $this->client->request('POST', $this->baseUrl . '/api/questionnaires', [
            'json' => [
                'title' => 'test questionnaire',
                'description' => 'test description',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(201, $response->getStatusCode()); //Api renvoi succès ?

        $headers = array_change_key_case($response->getHeaders(false), CASE_LOWER);
        $this->assertArrayHasKey('content-type', $headers);
        $this->assertStringContainsString(self::APP_JSON, $headers['content-type'][0]);

        $data = json_decode($response->getContent(false), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($data); //JSON correct ?
        $this->assertArrayHasKey('data', $data); //Réussite
        $this->assertSame('test questionnaire', $data['data']['title']);
        $this->assertSame('test description', $data['data']['description']);
    }

    public function testCreateQuestionnaireWithoutTitle(): void
    {
        $response = $this->client->request('POST', $this->baseUrl . '/api/questionnaires', [
            'json' => [
                'description' => 'test descriptionFail',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(400, $response->getStatusCode());

        $data = json_decode($response->getContent(false), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Title is required', $data['error']);
    }
}
