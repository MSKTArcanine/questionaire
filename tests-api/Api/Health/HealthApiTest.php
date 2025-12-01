<?php

namespace Tests;

class HealthApiTest extends AbstractApiTestCase
{
    public function testPingReturnsOk(): void
    {
        $response = $this->client->request('GET', $this->baseUrl . '/api/ping');

        $this->assertSame(200, $response->getStatusCode());
        $data = $response->toArray(false);

        $this->assertSame(['status' => 'ok'], $data);
    }
}
