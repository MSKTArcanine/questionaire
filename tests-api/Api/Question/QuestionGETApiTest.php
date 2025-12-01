<?php

namespace Tests;

final class QuestionGETApiTest extends AbstractApiTestCase
{
    public function testListQuestions(): void
    {
        $response = $this->client->request('GET', $this->baseUrl . '/api/questions', [
            'headers' => $this->authHeaders(true),
        ]);

        $this->assertSame(200, $response->getStatusCode());

        $data = $response->toArray(false);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
        $this->assertNotEmpty($data['data']);
    }

    public function testListQuestionsUnauthorized(): void
    {
        $response = $this->client->request('GET', $this->baseUrl . '/api/questions');

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testQuestionNotFound(): void
    {
        $response = $this->client->request('GET', $this->baseUrl . '/api/questions/999999', [
            'headers' => $this->authHeaders(true),
        ]);

        $this->assertSame(404, $response->getStatusCode());

        $data = $response->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Question not found', $data['error']);
    }

    public function testQuestionBase(): void
    {
        $listResponse = $this->client->request('GET', $this->baseUrl . '/api/questions', [
            'headers' => $this->authHeaders(true),
        ]);

        $this->assertSame(200, $listResponse->getStatusCode());

        $listData = $listResponse->toArray(false);
        $this->assertArrayHasKey('data', $listData);
        $this->assertIsArray($listData['data']);
        $this->assertNotEmpty($listData['data']);

        $firstQuestion = $listData['data'][0];
        $this->assertArrayHasKey('id', $firstQuestion);
        $questionId = $firstQuestion['id'];

        $response = $this->client->request('GET', $this->baseUrl . '/api/questions/' . $questionId, [
            'headers' => $this->authHeaders(true),
        ]);

        $this->assertSame(200, $response->getStatusCode());

        $data = $response->toArray(false);
        $this->assertArrayHasKey('data', $data);

        $question = $data['data'];

        $this->assertSame($questionId, $question['id']);
        $this->assertArrayHasKey('title', $question);
        $this->assertArrayHasKey('description', $question);
        $this->assertArrayHasKey('questionnaireId', $question);
    }

    public function testQuestionUnauthorized(): void
    {
        // On récupère un ID valide d'abord
        $listResponse = $this->client->request('GET', $this->baseUrl . '/api/questions', [
            'headers' => $this->authHeaders(true),
        ]);

        $this->assertSame(200, $listResponse->getStatusCode());
        $listData = $listResponse->toArray(false);
        $this->assertNotEmpty($listData['data']);

        $id = $listData['data'][0]['id'];

        // Puis on tente sans JWT
        $response = $this->client->request('GET', $this->baseUrl . '/api/questions/' . $id);

        $this->assertSame(401, $response->getStatusCode());
    }
}
