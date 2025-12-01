<?php

namespace Tests;

class QuestionnairePOSTApiTest extends AbstractApiTestCase
{
    public function testCreateQuestionnaires(): void
    {
        $response = $this->client->request('POST', $this->baseUrl . '/api/questionnaires', [
            'json' => [
                'title' => 'test questionnaire',
                'description' => 'test description',
            ],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders(true)
            ),
        ]);

        $this->assertSame(201, $response->getStatusCode());

        $headers = array_change_key_case($response->getHeaders(false), CASE_LOWER);
        $this->assertArrayHasKey('content-type', $headers);
        $this->assertStringContainsString(self::APP_JSON, $headers['content-type'][0]);

        $data = json_decode($response->getContent(false), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('data', $data);
        $this->assertSame('test questionnaire', $data['data']['title']);
        $this->assertSame('test description', $data['data']['description']);
    }

    public function testCreateQuestionnaireWithoutTitle(): void
    {
        $response = $this->client->request('POST', $this->baseUrl . '/api/questionnaires', [
            'json' => [
                'description' => 'test descriptionFail',
            ],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders(true)
            ),
        ]);

        $this->assertSame(400, $response->getStatusCode());

        $data = json_decode($response->getContent(false), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Title is required', $data['error']);
    }
}
