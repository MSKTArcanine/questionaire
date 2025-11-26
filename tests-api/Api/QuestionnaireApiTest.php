<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

class QuestionnaireApiTest extends TestCase
{
    private string $baseUrl = 'https://questionaire.localhost';

    public function testListQuestionnaires(): void
    {
        $client = HttpClient::create([
            'verify_peer' => false, //Self signed
            'verify_host' => false, //Pareil.
        ]);

        $response = $client->request('GET', $this->baseUrl . '/api/questionnaire/'); //curl ...

        $this->assertSame(200, $response->getStatusCode()); //Api renvoi succès ?

        $data = $response->toArray(); //Json -> Array

        $this->assertIsArray($data); //JSON correct ?
        $this->assertArrayHasKey('data', $data); //Réussite = 'data' => ...
        $this->assertIsArray($data['data']); //$data est bien un tablal.
    }

    public function testQuestionnaireNotFound(): void
    {
        $client = HttpClient::create([
            'verify_peer' => false,
            'verify_host' => false,
        ]);

        $response = $client->request('GET', $this->baseUrl . '/api/questionnaire/999999'); //Test 'débile'

        $this->assertSame(404, $response->getStatusCode()); //id not found = 404

        $data = $response->toArray(false); //Simple message error =>

        $this->assertArrayHasKey('error', $data); //Check la présence du error
        $this->assertSame('Questionnaire not found', $data['error']); //Ptet abusé sur ce test.
    }
}
