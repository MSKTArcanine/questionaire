<?php

namespace Tests;

class AuthMeApiTest extends AbstractApiTestCase
{
    public function testAuthMeUnauthorizedWithoutToken(): void
    {
        $response = $this->client->request('GET', $this->baseUrl . '/api/auth/me');

        $this->assertSame(401, $response->getStatusCode());
        $data = $response->toArray(false);

        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Unauthorized', $data['error']);
    }

    public function testAuthMeReturnsUserDataWithToken(): void
    {
        $response = $this->client->request('GET', $this->baseUrl . '/api/auth/me', [
            'headers' => $this->authHeaders(), // user
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $data = $response->toArray(false);

        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('roles', $data);
        $this->assertIsArray($data['roles']);
        $this->assertContains('ROLE_USER', $data['roles']);
    }
}
