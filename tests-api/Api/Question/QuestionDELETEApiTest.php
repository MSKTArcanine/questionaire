<?php

namespace Tests;

final class QuestionDELETEApiTest extends AbstractApiTestCase
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

        $createQuestion = $this->client->request('POST', $this->baseUrl . '/api/questions', [
            'json' => [
                'questionnaireId' => $questionnaireId,
                'title' => 'Question to delete',
                'description' => 'Description',
            ],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders(true)
            ),
        ]);

        $this->assertSame(201, $createQuestion->getStatusCode());
        $created = $createQuestion->toArray(false);

        return $created['data']['id'];
    }

    public function testDeleteQuestionSuccess(): void
    {
        $id = $this->createQuestionId();

        $deleteQuestion = $this->client->request('DELETE', $this->baseUrl . '/api/questions/' . $id, [
            'headers' => $this->authHeaders(true),
        ]);

        $this->assertSame(204, $deleteQuestion->getStatusCode());
        $this->assertSame('', $deleteQuestion->getContent(false));

        // Vérification que la question n'existe plus
        $getQuestion = $this->client->request('GET', $this->baseUrl . '/api/questions/' . $id, [
            'headers' => $this->authHeaders(true),
        ]);
        $this->assertSame(404, $getQuestion->getStatusCode());
    }

    public function testDeleteQuestionNotFound(): void
    {
        $deleteQuestion = $this->client->request('DELETE', $this->baseUrl . '/api/questions/999999', [
            'headers' => $this->authHeaders(true),
        ]);

        $this->assertSame(404, $deleteQuestion->getStatusCode());

        $data = $deleteQuestion->toArray(false);
        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Question not found', $data['error']);
    }

    public function testDeleteQuestionUnauthorized(): void
    {
        $id = $this->createQuestionId();

        $deleteQuestion = $this->client->request('DELETE', $this->baseUrl . '/api/questions/' . $id);

        $this->assertSame(401, $deleteQuestion->getStatusCode());
    }
}
