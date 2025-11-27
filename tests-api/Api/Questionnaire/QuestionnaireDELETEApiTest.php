<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class QuestionnaireDELETEApiTest extends TestCase
{
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

    public function testDeleteQuestionnaireSuccess(){
        $createQuestionnaire = $this->client->request('POST', $this->baseUrl . '/api/questionnaires', [
            'json' => [
                'title' => 'questionnaire to delete',
                'description' => 'description',
            ],
            'headers' => ['Content-Type' => 'application/json'],
        ]);
        
        $this->assertSame(201, $createQuestionnaire->getStatusCode());
        $created = $createQuestionnaire->toArray(false);
        $this->assertArrayHasKey('data', $created);
        $questionnaireId = $created['data']['id'];

        $deleteQuestionnaire = $this->client->request('DELETE', $this->baseUrl . '/api/questionnaires/' . $questionnaireId);
        $this->assertSame(204, $deleteQuestionnaire->getStatusCode());
        $this->assertSame('', $deleteQuestionnaire->getContent(false));

        $getQuestionnaire = $this->client->request('GET', $this->baseUrl . '/api/questionnaires/' . $questionnaireId);
        $this->assertSame(404, $getQuestionnaire->getStatusCode());
    }

    public function testDeleteQuestionnaireNotFound(){
        $deleteQuestionnaire = $this->client->request('DELETE', $this->baseUrl . '/api/questionnaires/999999');
        $this->assertSame(404, $deleteQuestionnaire->getStatusCode());

        $data = $deleteQuestionnaire->toArray(false);
        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Questionnaire not found', $data['error']);
    }
}
