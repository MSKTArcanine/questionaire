<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractApiTestCase extends TestCase
{
    protected const APP_JSON = 'application/json';

    protected string $baseUrl;
    protected HttpClientInterface $client;

    private ?string $userToken = null;
    private ?string $adminToken = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->baseUrl = $_ENV['TESTS_BASE_URL'] ?? 'https://questionaire.localhost';

        $this->client = HttpClient::create([
            'verify_peer' => false,
            'verify_host' => false,
        ]);
    }
    //Changement pour faire passer dans main

    // /api/auth/user et retourne le JWT
    protected function login(string $email, string $pin): string
    {
        $response = $this->client->request('POST', $this->baseUrl . '/api/auth/user', [
            'json' => [
                'email' => $email,
                'password' => $pin,
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(200, $response->getStatusCode(), 'Login doit renvoyer 200');

        $data = $response->toArray(false);
        $this->assertArrayHasKey('token', $data);
        //Check head
        return $data['token'];
    }

    //JWT pour un utilisateur + création si existe pas.
    protected function getUserToken(): string
    {
        if ($this->userToken === null) {
            $this->userToken = $this->login('usertest@example.com', '1234');
        }

        return $this->userToken;
    }

    //JWT admin
    protected function getAdminToken(): string
    {
        if ($this->adminToken === null) {
            $this->adminToken = $this->login('admin@example.com', 'admin');
        }

        return $this->adminToken;
    }

    //Token
    protected function authHeaders(bool $asAdmin = false): array
    {
        $token = $asAdmin ? $this->getAdminToken() : $this->getUserToken();

        return [
            'Authorization' => 'Bearer ' . $token,
        ];
    }
}
