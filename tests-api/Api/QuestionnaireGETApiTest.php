<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

class QuestionnaireGETApiTest extends TestCase
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

    public function testQuestionnaireBase():void{
        $client = HttpClient::create([
            'verify_peer' => false,
            'verify_host' => false,
        ]);
        $response = $client->request('GET', $this->baseUrl . '/api/questionnaire/1');
        $this->assertSame(200, $response->getStatusCode());

        $data = $response->toArray();
        
        $this->assertSame(1, $data['id']);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('rootQuestionId', $data);

        $this->assertArrayHasKey('questions', $data);
        $this->assertIsArray($data['questions']);
        $this->assertNotEmpty($data['questions']); //Pratique

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
