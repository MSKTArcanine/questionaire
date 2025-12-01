<?php

namespace Tests;

class ChoiceDELETEApiTest extends AbstractApiTestCase
{

    private function createQuestionId(): int
    {

        $responseQuestionnaire = $this->client->request('POST', $this->baseUrl . '/api/questionnaires', [
            'json' => [
                'title' => 'questionnaire for question',
                'description' => 'description',
            ],
            'headers' => array_merge(['Content-Type' => self::APP_JSON], $this->authHeaders(true)),
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
            'headers' => array_merge(['Content-Type' => self::APP_JSON], $this->authHeaders(true)),
        ]);

        $this->assertSame(201, $responseQuestion->getStatusCode());
        $dataQuestion = json_decode($responseQuestion->getContent(false), true, 512, JSON_THROW_ON_ERROR);
        return $dataQuestion['data']['id'];
    }

    public function testDeleteQuestionSuccess(): void
    {
        $questionId = $this->createQuestionId();

        $createChoice = $this->client->request('POST', $this->baseUrl . '/api/choices', [
            'json' => [
                'questionId' => $questionId,
                'content' => 'Choice to delete',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(201, $createChoice->getStatusCode());
        $created = $createChoice->toArray(false);
        $id = $created['data']['id'];

        $deleteChoice = $this->client->request('DELETE', $this->baseUrl . '/api/choices/' . $id);
        $this->assertSame(204, $deleteChoice->getStatusCode());
        $data = $deleteChoice->getContent(false);
        $this->assertSame('', $data);
    }

    public function testDeleteChoiceNotFound(): void
    {
        $deleteChoice = $this->client->request('DELETE', $this->baseUrl . '/api/choices/999999');
        $this->assertSame(404, $deleteChoice->getStatusCode());

        $data = $deleteChoice->toArray(false);
        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Choice not found', $data['error']);
    }
}
