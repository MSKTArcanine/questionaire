<?php

namespace Tests;

class AnswerSessionApiTest extends AbstractApiTestCase
{
    private ?string $sessionUserToken = null;

    protected function getUserToken(): string
    {
        if ($this->sessionUserToken === null) {
            $email = 'session-user-' . uniqid('', true) . '@example.com';
            $this->sessionUserToken = $this->login($email, '1234');
        }

        return $this->sessionUserToken;
    }

    private function getFirstQuestionnaireSlug(): string
    {
        // Questionnaire avec root + choices
        $firstWithRoot = $this->findFirstQuestionnaireWithRoot();
        if ($firstWithRoot !== null) {
            $this->assertArrayHasKey('slug', $firstWithRoot);
            return $firstWithRoot['slug'];
        }

        // Creer un nouveau sinon
        return $this->createQuestionnaireWithRootAndChoice();
    }

    private function findFirstQuestionnaireWithRoot(): ?array
    {
        $response = $this->client->request('GET', $this->baseUrl . '/api/questionnaires', [
            'headers' => $this->authHeaders(),
        ]);
        $this->assertSame(200, $response->getStatusCode());
        $data = $response->toArray(false);

        $this->assertArrayHasKey('data', $data);
        $this->assertNotEmpty($data['data']);

        foreach ($data['data'] as $item) {
            if ($this->questionnaireRootHasChoices($item)) {
                return $item;
            }
        }

        return null;
    }

    private function createQuestionnaireWithRootAndChoice(): string
    {
        $createResponse = $this->client->request('POST', $this->baseUrl . '/api/questionnaires', [
            'json' => ['title' => 'test questionnaire', 'description' => 'test description'],
            'headers' => array_merge(['Content-Type' => self::APP_JSON], $this->authHeaders()),
        ]);
        $this->assertSame(201, $createResponse->getStatusCode());
        $created = $createResponse->toArray(false)['data'];

        // creer root
        $qResponse = $this->client->request('POST', $this->baseUrl . '/api/questions', [
            'json' => ['title' => 'root question', 'questionnaireId' => $created['id']],
            'headers' => array_merge(['Content-Type' => self::APP_JSON], $this->authHeaders()),
        ]);
        $this->assertSame(201, $qResponse->getStatusCode());
        $question = $qResponse->toArray(false)['data'];

        // 1 choix mini
        $cResponse = $this->client->request('POST', $this->baseUrl . '/api/choices', [
            'json' => ['questionId' => $question['id'], 'content' => 'choice-1'],
            'headers' => array_merge(['Content-Type' => self::APP_JSON], $this->authHeaders(true)),
        ]);
        $this->assertSame(201, $cResponse->getStatusCode());

        // return le slug
        $detail = $this->client->request('GET', $this->baseUrl . '/api/questionnaires/' . $created['id'], [
            'headers' => $this->authHeaders(),
        ]);
        $this->assertSame(200, $detail->getStatusCode());
        $details = $detail->toArray(false)['data'];
        $this->assertArrayHasKey('slug', $details);
        return $details['slug'];
    }

    private function questionnaireRootHasChoices(array $item): bool
    {
        if (!isset($item['rootQuestionId']) || $item['rootQuestionId'] === null) {
            return false;
        }

        $detailResponse = $this->client->request('GET', $this->baseUrl . '/api/questionnaires/' . $item['id'], [
            'headers' => $this->authHeaders(),
        ]);
        $this->assertSame(200, $detailResponse->getStatusCode());
        $details = $detailResponse->toArray(false)['data'] ?? [];
        $rootQuestionId = $item['rootQuestionId'];

        foreach ($details['questions'] as $q) {
            if ($q['id'] === $rootQuestionId) {
                return !empty($q['choices']);
            }
        }

        return false;
    }

    public function testCreateSessionUnauthorizedWithoutJwt(): void
    {
        $response = $this->client->request('POST', $this->baseUrl . '/api/sessions', [
            'json' => ['slug' => 'whatever'],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(401, $response->getStatusCode());
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

        // création
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

        // réutilisation => doit renvoyer 200 avec la même session
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
        $response = $this->client->request(
            'GET',
            $this->baseUrl . '/api/sessions/00000000-0000-0000-0000-000000000000',
            [
                'headers' => $this->authHeaders(),
            ]
        );

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

        $getResponse = $this->client->request('GET', $this->baseUrl . '/api/sessions/' . $sessionId, [
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

        $answerResponse = $this->client->request(
            'POST',
            $this->baseUrl . '/api/sessions/' . $sessionId . '/answers',
            [
                'json' => ['choiceId' => $choiceId],
                'headers' => array_merge(
                    ['Content-Type' => self::APP_JSON],
                    $this->authHeaders()
                ),
            ]
        );

        $this->assertSame(200, $answerResponse->getStatusCode());
        $answerData = $answerResponse->toArray(false);

        $this->assertArrayHasKey('data', $answerData);
        $returnedSession = $answerData['data'];
        $this->assertArrayHasKey('finished', $returnedSession);
        // finished peut être true ou false, selon la branche
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

        $answerResponse = $this->client->request(
            'POST',
            $this->baseUrl . '/api/sessions/' . $sessionId . '/answers',
            [
                'json' => ['choiceId' => 999999],
                'headers' => array_merge(
                    ['Content-Type' => self::APP_JSON],
                    $this->authHeaders()
                ),
            ]
        );

        $this->assertSame(404, $answerResponse->getStatusCode());
        $data = $answerResponse->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Choice not found', $data['error']);
    }
}
