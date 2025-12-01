<?php

namespace Tests;

final class QuestionUPDATEApiTest extends AbstractApiTestCase
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

    private function createQuestionId(): int
    {
        $questionnaireId = $this->createQuestionnaireId();

        $response = $this->client->request('POST', $this->baseUrl . '/api/questions', [
            'json' => [
                'questionnaireId' => $questionnaireId,
                'title' => 'Original Title',
                'description' => 'Original Description',
            ],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders(true)
            ),
        ]);

        $this->assertSame(201, $response->getStatusCode());
        $data = $response->toArray(false);

        return $data['data']['id'];
    }

    public function testUpdateQuestionSuccess(): void
    {
        $id = $this->createQuestionId();

        $updateQuestion = $this->client->request('PUT', $this->baseUrl . '/api/questions/' . $id, [
            'json' => [
                'title' => 'Updated Title',
                'description' => 'Updated Description',
            ],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders(true)
            ),
        ]);

        $this->assertSame(200, $updateQuestion->getStatusCode());
        $data = $updateQuestion->toArray(false);
        $question = $data['data'];

        $this->assertSame('Updated Title', $question['title']);
        $this->assertSame('Updated Description', $question['description']);
    }

    public function testUpdateQuestionNotFound(): void
    {
        $updateQuestion = $this->client->request('PUT', $this->baseUrl . '/api/questions/999999', [
            'json' => [
                'title' => 'osef',
            ],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders(true)
            ),
        ]);

        $this->assertSame(404, $updateQuestion->getStatusCode());
        $data = $updateQuestion->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Question not found', $data['error']);
    }

    public function testUpdateQuestionInvalidQuestionnaire(): void
    {
        $id = $this->createQuestionId();

        $updateQuestion = $this->client->request('PUT', $this->baseUrl . '/api/questions/' . $id, [
            'json' => [
                'questionnaireId' => 999999,
            ],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders(true)
            ),
        ]);

        $this->assertSame(405, $updateQuestion->getStatusCode());
        $data = $updateQuestion->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Invalide questionnaire', $data['error']);
    }

    public function testUpdateQuestionUnauthorized(): void
    {
        $id = $this->createQuestionId();

        $updateQuestion = $this->client->request('PUT', $this->baseUrl . '/api/questions/' . $id, [
            'json' => [
                'title' => 'Nope',
            ],
            'headers' => [
                'Content-Type' => self::APP_JSON,
            ],
        ]);

        $this->assertSame(401, $updateQuestion->getStatusCode());
    }
}
