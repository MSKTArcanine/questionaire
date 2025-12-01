<?php

namespace Tests;

class UserAuthApiTest extends AbstractApiTestCase
{
    public function testUserLoginCreatesUserAndReturnsToken(): void
    {
        $email = 'new-user-' . uniqid('', true) . '@example.com';

        $response = $this->client->request('POST', $this->baseUrl . '/api/auth/user', [
            'json' => [
                'email' => $email,
                'password' => '9999',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $data = $response->toArray(false);

        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }

    public function testUserLoginMissingFields(): void
    {
        $response = $this->client->request('POST', $this->baseUrl . '/api/auth/user', [
            'json' => [
                'email' => 'incomplete@example.com',
                // pas de password
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(400, $response->getStatusCode());
        $data = $response->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Email et PIN requis', $data['error']);
    }

    public function testUserLoginWrongPinReturns401(): void
    {
        $email = 'wrong-pin-' . uniqid('', true) . '@example.com';

        // création + bon pin
        $first = $this->client->request('POST', $this->baseUrl . '/api/auth/user', [
            'json' => [
                'email' => $email,
                'password' => 'correct-pin',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(200, $first->getStatusCode());

        // Mauvais pin
        $response = $this->client->request('POST', $this->baseUrl . '/api/auth/user', [
            'json' => [
                'email' => $email,
                'password' => 'bad-pin',
            ],
            'headers' => ['Content-Type' => self::APP_JSON],
        ]);

        $this->assertSame(401, $response->getStatusCode());
        $data = $response->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('PIN invalide', $data['error']);
    }
}
