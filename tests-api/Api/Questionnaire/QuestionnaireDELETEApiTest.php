<?php

namespace Tests;

class QuestionnaireDELETEApiTest extends AbstractApiTestCase
{
    public function testDeleteQuestionnaireSuccess(): void
    {
        // création (admin)
        $createQuestionnaire = $this->client->request('POST', $this->baseUrl . '/api/questionnaires', [
            'json' => [
                'title'       => 'questionnaire to delete',
                'description' => 'description',
            ],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders(true)
            ),
        ]);

        $this->assertSame(201, $createQuestionnaire->getStatusCode());
        $created = $createQuestionnaire->toArray(false);
        $this->assertArrayHasKey('data', $created);
        $questionnaireId = $created['data']['id'];

        // delete (admin)
        $deleteQuestionnaire = $this->client->request(
            'DELETE',
            $this->baseUrl . '/api/questionnaires/' . $questionnaireId,
            [
                'headers' => $this->authHeaders(true),
            ]
        );
        $this->assertSame(204, $deleteQuestionnaire->getStatusCode());
        $this->assertSame('', $deleteQuestionnaire->getContent(false));

        // vérif derrière : not found
        $getQuestionnaire = $this->client->request(
            'GET',
            $this->baseUrl . '/api/questionnaires/' . $questionnaireId,
            [
                'headers' => $this->authHeaders(),
            ]
        );
        $this->assertSame(404, $getQuestionnaire->getStatusCode());
    }

    public function testDeleteQuestionnaireNotFound(): void
    {
        $deleteQuestionnaire = $this->client->request(
            'DELETE',
            $this->baseUrl . '/api/questionnaires/999999',
            [
                'headers' => $this->authHeaders(true),
            ]
        );
        $this->assertSame(404, $deleteQuestionnaire->getStatusCode());

        $data = $deleteQuestionnaire->toArray(false);
        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Questionnaire not found', $data['error']);
    }

    public function testDeleteQuestionnaireUnauthorized(): void
    {
        // Pas de header Authorization
        $deleteQuestionnaire = $this->client->request(
            'DELETE',
            $this->baseUrl . '/api/questionnaires/1'
        );

        $this->assertSame(401, $deleteQuestionnaire->getStatusCode());
    }
}
