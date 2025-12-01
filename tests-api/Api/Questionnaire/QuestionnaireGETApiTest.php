<?php

namespace Tests;

class QuestionnaireGETApiTest extends AbstractApiTestCase
{
    public function testListQuestionnaires(): void
    {
        $response = $this->client->request('GET', $this->baseUrl . '/api/questionnaires', [
            'headers' => $this->authHeaders(), // user
        ]);

        $this->assertSame(200, $response->getStatusCode());

        $data = $response->toArray();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
    }

    public function testListQuestionnairesUnauthorized(): void
    {
        $response = $this->client->request('GET', $this->baseUrl . '/api/questionnaires');

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testQuestionnaireNotFound(): void
    {
        $response = $this->client->request('GET', $this->baseUrl . '/api/questionnaires/999999', [
            'headers' => $this->authHeaders(),
        ]);

        $this->assertSame(404, $response->getStatusCode());

        $data = $response->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Questionnaire not found', $data['error']);
    }

    public function testQuestionnaireBase(): void
    {
        // liste
        $listResponse = $this->client->request('GET', $this->baseUrl . '/api/questionnaires', [
            'headers' => $this->authHeaders(),
        ]);
        $this->assertSame(200, $listResponse->getStatusCode());

        $listData = $listResponse->toArray();
        $this->assertIsArray($listData);
        $this->assertArrayHasKey('data', $listData);
        $this->assertNotEmpty($listData['data']);

        // Find questionnaire sinon création
        $firstQuestionnaire = null;
        foreach ($listData['data'] as $item) {
            if (isset($item['rootQuestionId']) && $item['rootQuestionId'] !== null) {
                $detail = $this->client->request('GET', $this->baseUrl . '/api/questionnaires/' . $item['id'], [
                    'headers' => $this->authHeaders(),
                ]);
                $this->assertSame(200, $detail->getStatusCode());
                $details = $detail->toArray(false)['data'];
                if (!empty($details['questions'])) {
                    $firstQuestionnaire = $item;
                    break;
                }
            }
        }
        if ($firstQuestionnaire === null) {
            $createResponse = $this->client->request('POST', $this->baseUrl . '/api/questionnaires', [
                'json' => ['title' => 'test questionnaire', 'description' => 'test description'],
                'headers' => array_merge(['Content-Type' => 'application/json'], $this->authHeaders(true)),
            ]);
            $this->assertSame(201, $createResponse->getStatusCode());
            $created = $createResponse->toArray(false)['data'];

            $qResponse = $this->client->request('POST', $this->baseUrl . '/api/questions', [
                'json' => ['questionnaireId' => $created['id'], 'title' => 'Root Q', 'description' => 'D'],
                'headers' => array_merge(['Content-Type' => 'application/json'], $this->authHeaders(true)),
            ]);
            $this->assertSame(201, $qResponse->getStatusCode());
            $question = $qResponse->toArray(false)['data'];

            // creer choix pour root
            $cResponse = $this->client->request('POST', $this->baseUrl . '/api/choices', [
                'json' => ['questionId' => $question['id'], 'content' => 'Choice 1'],
                'headers' => array_merge(['Content-Type' => 'application/json'], $this->authHeaders(true)),
            ]);
            $this->assertSame(201, $cResponse->getStatusCode());

            // details = Questions + Choices
            $detailCheck = $this->client->request('GET', $this->baseUrl . '/api/questionnaires/' . $created['id'], [
                'headers' => $this->authHeaders(true),
            ]);
            $this->assertSame(200, $detailCheck->getStatusCode());
            $detailsData = $detailCheck->toArray(false)['data'] ?? null;
            $this->assertNotNull($detailsData);
            $this->assertArrayHasKey('questions', $detailsData);
            $this->assertNotEmpty($detailsData['questions']);
            $firstQuestionnaire = $created;
        }

        $this->assertArrayHasKey('id', $firstQuestionnaire);
        $id = $firstQuestionnaire['id'];

        // détail
        $response = $this->client->request('GET', $this->baseUrl . '/api/questionnaires/' . $id, [
            'headers' => $this->authHeaders(),
        ]);
        $this->assertSame(200, $response->getStatusCode());

        $data = $response->toArray();
        $this->assertIsArray($data);
        $this->assertArrayHasKey('data', $data);
        $data = $data['data'];

        $this->assertSame($id, $data['id']);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('rootQuestionId', $data);

        $this->assertArrayHasKey('questions', $data);
        $this->assertIsArray($data['questions']);
        $this->assertNotEmpty($data['questions']);

        $firstQuestion = $data['questions'][0];
        $this->assertArrayHasKey('id', $firstQuestion);
        $this->assertArrayHasKey('title', $firstQuestion);
        $this->assertArrayHasKey('choices', $firstQuestion);
        $this->assertIsArray($firstQuestion['choices']);

        $firstChoice = $firstQuestion['choices'][0];
        $this->assertArrayHasKey('id', $firstChoice);
        $this->assertArrayHasKey('content', $firstChoice);
        $this->assertArrayHasKey('nextQuestionId', $firstChoice);
    }
}
