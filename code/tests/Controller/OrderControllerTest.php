<?php

namespace App\Tests\Controller;

use ApiTestCase\JsonApiTestCase;

class OrderControllerTest extends JsonApiTestCase
{
    public function testEmptyList()
    {
        $client = static::createClient();

        $client->request('GET', '/orders?limit=10&page=1');

        $this->assertEquals('[]', $client->getResponse()->getContent());
    }

    public function testIncorrectLimitList()
    {
        $client = static::createClient();

        $client->request('GET', '/orders?limit=0&page=1');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testIncorrectPageList()
    {
        $client = static::createClient();

        $client->request('GET', '/orders?limit=10&page=0');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testCreate()
    {
        $client = static::createClient();

        $client->request('POST', '/orders', [
            'origin' => ['22.334600','114.147636'],
            'destination' => ['22.421550','114.171112']
        ]);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponse($client->getResponse(), 'create');
    }

    public function testIncorrectDestinationCreate()
    {
        $client = static::createClient();

        $client->request('POST', '/orders', [
            'origin' => ['22.334600','114.147636'],
            'destination' => ['22.421550','194.171112']
        ]);

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testIncorrectOriginCreate()
    {
        $client = static::createClient();

        $client->request('POST', '/orders', [
            'origin' => ['92.334600','114.147636'],
            'destination' => ['22.421550','194.171112']
        ]);

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testList()
    {
        $client = static::createClient();

        $client->request('GET', '/orders?limit=10&page=1');

        $this->assertResponse($client->getResponse(), 'list');
    }

    public function testTake()
    {
        $client = static::createClient();

        $client->request('PATCH', '/orders/1', ['status' => 'TAKEN']);

        $this->assertResponse($client->getResponse(), 'take');
    }
}