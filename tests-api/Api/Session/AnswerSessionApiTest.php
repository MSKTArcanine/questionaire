<?php

namespace Tests;

class AnswerSessionApiTest extends AbstractApiTestCase
{
    private function getFirstQuestionnaireSlug(): string
    {
        $response = $this->client->request('GET', $this->baseUrl . '/api/questionnaires', [
            'headers' => $this->authHeaders(),
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $data = $response->toArray(false);

        $this->assertArrayHasKey('data', $data);
        $this->assertNotEmpty($data['data']);

        $first = $data['data'][0];
        $this->assertArrayHasKey('slug', $first);

        return $first['slug'];
    }

    public function testCreateSessionUnauthorizedWithoutJwt(): void
    {
        $response = $this->client->request('POST', $this->baseUrl . '/api/sessions', [
            'json' => ['slug' => 'whatever'],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(401, $response->getStatusCode());
        $data = $response->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Unauthorized', $data['error']);
    }

    public function testCreateSessionQuestionnaireNotFound(): void
    {
        $response = $this->client->request('POST', $this->baseUrl . '/api/sessions', [
            'json' => ['slug' => '00000000-0000-0000-0000-000000000000'],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders()
            ),
        ]);

        $this->assertSame(404, $response->getStatusCode());
        $data = $response->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Questionnaire not found', $data['error']);
    }

    public function testCreateSessionSuccessAndReuseIncomplete(): void
    {
        $slug = $this->getFirstQuestionnaireSlug();

        // 1) création
        $firstResponse = $this->client->request('POST', $this->baseUrl . '/api/sessions', [
            'json' => ['slug' => $slug],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders()
            ),
        ]);

        $this->assertSame(201, $firstResponse->getStatusCode());
        $firstData = $firstResponse->toArray(false);
        $this->assertArrayHasKey('data', $firstData);

        $session = $firstData['data'];
        $this->assertArrayHasKey('id', $session);
        $this->assertArrayHasKey('current_question', $session);
        $this->assertFalse($session['finished']);

        $sessionId = $session['id'];

        // 2) réutilisation => 200
        $secondResponse = $this->client->request('POST', $this->baseUrl . '/api/sessions', [
            'json' => ['slug' => $slug],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders()
            ),
        ]);

        $this->assertSame(200, $secondResponse->getStatusCode());
        $secondData = $secondResponse->toArray(false);
        $this->assertArrayHasKey('data', $secondData);

        $secondSession = $secondData['data'];
        $this->assertSame($sessionId, $secondSession['id']);
    }

    public function testGetAnswerSessionNotFound(): void
    {
        $response = $this->client->request('GET', $this->baseUrl . '/api/sessions/00000000-0000-0000-0000-000000000000', [
            'headers' => $this->authHeaders(),
        ]);

        $this->assertSame(404, $response->getStatusCode());
        $data = $response->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('AnswerSession not found', $data['error']);
    }

    public function testGetAnswerSessionSuccess(): void
    {
        $slug = $this->getFirstQuestionnaireSlug();

        $sessionResponse = $this->client->request('POST', $this->baseUrl . '/api/sessions', [
            'json' => ['slug' => $slug],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders()
            ),
        ]);

        $this->assertSame(201, $sessionResponse->getStatusCode());
        $sessionData = $sessionResponse->toArray(false)['data'];
        $sessionId = $sessionData['id'];

        $getResponse = $this->client->request('GET', $this->baseUrl . '/api/sessions' . $sessionId, [
            'headers' => $this->authHeaders(),
        ]);

        $this->assertSame(200, $getResponse->getStatusCode());
        $getData = $getResponse->toArray(false);

        $this->assertArrayHasKey('data', $getData);
        $getSession = $getData['data'];
        $this->assertSame($sessionId, $getSession['id']);
    }

    public function testPostAnswersSuccess(): void
    {
        $slug = $this->getFirstQuestionnaireSlug();

        // créer une session
        $sessionResponse = $this->client->request('POST', $this->baseUrl . '/api/sessions', [
            'json' => ['slug' => $slug],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders()
            ),
        ]);

        $this->assertSame(201, $sessionResponse->getStatusCode());
        $session = $sessionResponse->toArray(false)['data'];

        $sessionId = $session['id'];
        $currentQuestion = $session['current_question'];

        $this->assertNotNull($currentQuestion);
        $this->assertArrayHasKey('choices', $currentQuestion);
        $this->assertNotEmpty($currentQuestion['choices']);

        $choiceId = $currentQuestion['choices'][0]['id'];

        $answerResponse = $this->client->request('POST', $this->baseUrl . '/api/sessions/' . $sessionId . '/answers', [
            'json' => ['choiceId' => $choiceId],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders()
            ),
        ]);

        $this->assertSame(200, $answerResponse->getStatusCode());
        $answerData = $answerResponse->toArray(false);

        $this->assertArrayHasKey('data', $answerData);
        $returnedSession = $answerData['data'];
        $this->assertArrayHasKey('finished', $returnedSession);
        // finished peut être true ou false
    }

    public function testPostAnswersChoiceNotFound(): void
    {
        $slug = $this->getFirstQuestionnaireSlug();

        // créer une session
        $sessionResponse = $this->client->request('POST', $this->baseUrl . '/api/sessions', [
            'json' => ['slug' => $slug],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders()
            ),
        ]);

        $this->assertSame(201, $sessionResponse->getStatusCode());
        $session = $sessionResponse->toArray(false)['data'];
        $sessionId = $session['id'];

        $answerResponse = $this->client->request('POST', $this->baseUrl . '/api/sessions/' . $sessionId . '/answers', [
            'json' => ['choiceId' => 999999],
            'headers' => array_merge(
                ['Content-Type' => self::APP_JSON],
                $this->authHeaders()
            ),
        ]);

        $this->assertSame(404, $answerResponse->getStatusCode());
        $data = $answerResponse->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Choice not found', $data['error']);
    }
}
