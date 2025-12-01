<?php

namespace Tests;

class AdminDashBoardAptiTest extends AbstractApiTestCase
{
    private function createQuestionnaireWithQuestionAndChoice(): array
    {
        // Create questionnaire (admin)
        $createQuestionnaire = $this->client->request('POST', $this->baseUrl . '/api/questionnaires', [
            'json' => [
                'title' => 'admin test questionnaire',
                'description' => 'desc',
            ],
            'headers' => array_merge(['Content-Type' => self::APP_JSON], $this->authHeaders(true)),
        ]);
        $this->assertSame(201, $createQuestionnaire->getStatusCode());
        $created = $createQuestionnaire->toArray(false)['data'];

        // Create question (admin) — becomes root
        $qResponse = $this->client->request('POST', $this->baseUrl . '/api/questions', [
            'json' => ['questionnaireId' => $created['id'], 'title' => 'Root question', 'description' => 'root desc'],
            'headers' => array_merge(['Content-Type' => self::APP_JSON], $this->authHeaders(true)),
        ]);
        $this->assertSame(201, $qResponse->getStatusCode());
        $question = $qResponse->toArray(false)['data'];

        // Create choice (admin)
        $cResponse = $this->client->request('POST', $this->baseUrl . '/api/choices', [
            'json' => ['questionId' => $question['id'], 'content' => 'Choice 1'],
            'headers' => array_merge(['Content-Type' => self::APP_JSON], $this->authHeaders(true)),
        ]);
        $this->assertSame(201, $cResponse->getStatusCode());
        $choice = $cResponse->toArray(false)['data'];

        // Fetch the questionnaire slug via detail endpoint
        $detail = $this->client->request('GET', $this->baseUrl . '/api/questionnaires/' . $created['id'], [
            'headers' => $this->authHeaders(true),
        ]);
        $this->assertSame(200, $detail->getStatusCode());
        $details = $detail->toArray(false)['data'];

        return [
            'questionnaire' => $created,
            'question' => $question,
            'choice' => $choice,
            'slug' => $details['slug'],
        ];
    }

    private function createSessionAndAnswer(string $slug, int $choiceId): array
    {
        // Create a session as a user
        $sessionResponse = $this->client->request('POST', $this->baseUrl . '/api/sessions', [
            'json' => ['slug' => $slug],
            'headers' => array_merge(['Content-Type' => self::APP_JSON], $this->authHeaders()),
        ]);
        $this->assertSame(201, $sessionResponse->getStatusCode());
        $session = $sessionResponse->toArray(false)['data'];

        // Post an answer
        $answerResponse = $this->client->request('POST', $this->baseUrl . '/api/sessions/' . $session['id'] . '/answers', [
            'json' => ['choiceId' => $choiceId],
            'headers' => array_merge(['Content-Type' => self::APP_JSON], $this->authHeaders()),
        ]);
        $this->assertSame(200, $answerResponse->getStatusCode());
        $answerSession = $answerResponse->toArray(false)['data'];

        return [$session, $answerSession];
    }

    public function testDashboardUnauthorizedWithoutToken(): void
    {
        $response = $this->client->request('GET', $this->baseUrl . '/api/admin/dashboard');
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testDashboardReturnsListWithAdmin(): void
    {
        $data = $this->createQuestionnaireWithQuestionAndChoice();

        // Create a session (user) for this questionnaire so dashboard stats will reflect it
        $this->createSessionAndAnswer($data['slug'], $data['choice']['id']);

        // Request dashboard as admin
        $response = $this->client->request('GET', $this->baseUrl . '/api/admin/dashboard', [
            'headers' => $this->authHeaders(true),
        ]);
        $this->assertSame(200, $response->getStatusCode());
        $body = $response->toArray(false);
        $this->assertArrayHasKey('data', $body);
        $this->assertIsArray($body['data']);
        $this->assertNotEmpty($body['data']);

        // Find the created questionnaire
        $found = null;
        foreach ($body['data'] as $item) {
            if ($item['id'] === $data['questionnaire']['id']) {
                $found = $item;
                break;
            }
        }
        $this->assertNotNull($found);
        $this->assertArrayHasKey('total_sessions', $found);
        $this->assertGreaterThanOrEqual(1, $found['total_sessions']);
        $this->assertArrayHasKey('finished_sessions', $found);
        $this->assertArrayHasKey('completion_rate', $found);
    }

    public function testQuestionnaireStatsAndExportCsv(): void
    {
        $data = $this->createQuestionnaireWithQuestionAndChoice();
        $slug = $data['slug'];
        $questionnaireId = $data['questionnaire']['id'];
        $choiceId = $data['choice']['id'];

        // creation + popu
        $this->createSessionAndAnswer($slug, $choiceId);

        // Stats
        $response = $this->client->request('GET', $this->baseUrl . '/api/admin/questionnaires/' . $questionnaireId . '/stats', [
            'headers' => $this->authHeaders(true),
        ]);
        $this->assertSame(200, $response->getStatusCode());
        $body = $response->toArray(false);
        $this->assertArrayHasKey('questionnaire', $body);
        $this->assertSame($questionnaireId, $body['questionnaire']['id']);
        $this->assertArrayHasKey('questions', $body);
        $this->assertIsArray($body['questions']);
        $this->assertNotEmpty($body['questions']);

        // Choice count = 1
        $qStat = $body['questions'][0];
        $this->assertArrayHasKey('choices', $qStat);
        $this->assertIsArray($qStat['choices']);
        $this->assertNotEmpty($qStat['choices']);
        $choiceStat = $qStat['choices'][0];
        $this->assertArrayHasKey('count', $choiceStat);
        $this->assertGreaterThanOrEqual(1, $choiceStat['count']);

        // Export CSV, cébo
        $export = $this->client->request('GET', $this->baseUrl . '/api/admin/questionnaires/' . $questionnaireId . '/export', [
            'headers' => $this->authHeaders(true),
        ]);
        $this->assertSame(200, $export->getStatusCode());
        $headers = array_change_key_case($export->getHeaders(false), CASE_LOWER);
        $this->assertArrayHasKey('content-type', $headers);
        $this->assertStringContainsString('text/csv', $headers['content-type'][0]);
        $content = $export->getContent(false);
        $this->assertStringContainsString('session_id', $content);
        $this->assertStringContainsString('question_title', $content);
    }
}
