<?php

namespace Tests;

class ChoiceGETApiTest extends AbstractApiTestCase
{

    public function testListChoices(): void
    {
        $response = $this->client->request('GET', $this->baseUrl . '/api/choices');

        $this->assertSame(200, $response->getStatusCode());

        $data = $response->toArray();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
        $this->assertNotEmpty($data['data']);
    }

    public function testChoiceNotFound(): void
    {
        $response = $this->client->request('GET', $this->baseUrl . '/api/choices/999999');

        $this->assertSame(404, $response->getStatusCode());

        $data = $response->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Choice not found', $data['error']);
    }

    public function testChoiceBase(): void
    {
        $listResponse = $this->client->request('GET', $this->baseUrl . '/api/choices');
        $this->assertSame(200, $listResponse->getStatusCode());

        $listData = $listResponse->toArray();
        $this->assertArrayHasKey('data', $listData);
        $this->assertIsArray($listData['data']);
        $this->assertNotEmpty($listData['data']);

        $firstChoice = $listData['data'][0];
        $this->assertArrayHasKey('id', $firstChoice);
        $choiceId = $firstChoice['id'];

        $response = $this->client->request('GET', $this->baseUrl . '/api/choices/' . $choiceId);
        $this->assertSame(200, $response->getStatusCode());

        $data = $response->toArray();
        $this->assertArrayHasKey('data', $data);
        $choice = $data['data'];

        $this->assertSame($choiceId, $choice['id']);
        $this->assertArrayHasKey('content', $choice);
        $this->assertArrayHasKey('questionId', $choice);
        $this->assertArrayHasKey('nextQuestionId', $choice);
    }
}
