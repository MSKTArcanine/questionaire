<?php

namespace Tests;

class AuthMeApiTest extends AbstractApiTestCase
{
    public function testAuthMeUnauthorizedWithoutToken(): void
    {
        $response = $this->client->request('GET', $this->baseUrl . '/api/auth/me');

        $this->assertSame(401, $response->getStatusCode());
        $data = $response->toArray(false);

        if (isset($data['error'])) {
            $this->assertSame('Unauthorized', $data['error']);
        } else {
            $this->assertArrayHasKey('message', $data);
            $this->assertNotEmpty($data['message']);
        }
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
