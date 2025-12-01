<?php

namespace Tests;

final class QuestionPOSTApiTest extends AbstractApiTestCase
{
    private function createQuestionnaireId(): int
    {
        $response = $this->client->request('POST', $this->baseUrl . '/api/questionnaires', [
            'json' => [
                'title' => 'questionnaire for question',
                'description' => 'description',
            ],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders(true)
            ),
        ]);

        $this->assertSame(201, $response->getStatusCode());
        $data = json_decode($response->getContent(false), true, 512, JSON_THROW_ON_ERROR);

        return $data['data']['id'];
    }

    public function testCreateQuestionSuccess(): void
    {
        $questionnaireId = $this->createQuestionnaireId();

        $response = $this->client->request('POST', $this->baseUrl . '/api/questions', [
            'json' => [
                'questionnaireId' => $questionnaireId,
                'title' => 'My question',
                'description' => 'text',
            ],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders(true)
            ),
        ]);

        $this->assertSame(201, $response->getStatusCode());

        $data = $response->toArray(false);
        $this->assertArrayHasKey('data', $data);
        $question = $data['data'];

        $this->assertSame('My question', $question['title']);
        $this->assertSame('text', $question['description']);
        $this->assertSame($questionnaireId, $question['questionnaireId']);
    }

    public function testCreateQuestionInvalidForm(): void
    {
        $response = $this->client->request('POST', $this->baseUrl . '/api/questions', [
            'json' => [
                'description' => 'text',
            ],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders(true)
            ),
        ]);

        $this->assertSame(405, $response->getStatusCode());

        $data = $response->toArray(false);
        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Invalid form', $data['error']);
    }

    public function testCreateQuestionInvalidQuestionnaireId(): void
    {
        $response = $this->client->request('POST', $this->baseUrl . '/api/questions', [
            'json' => [
                'questionnaireId' => 999999,
                'title' => 'My question',
                'description' => 'text',
            ],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders(true)
            ),
        ]);

        $this->assertSame(405, $response->getStatusCode());

        $data = $response->toArray(false);
        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Questionnaire not found', $data['error']);
    }

    public function testCreateQuestionUnauthorized(): void
    {
        $response = $this->client->request('POST', $this->baseUrl . '/api/questions', [
            'json' => [
                'questionnaireId' => 123,
                'title' => 'My question',
                'description' => 'text',
            ],
            'headers' => [
                'Content-Type' => self::APP_JSON,
            ],
        ]);

        $this->assertSame(401, $response->getStatusCode());
    }
}
