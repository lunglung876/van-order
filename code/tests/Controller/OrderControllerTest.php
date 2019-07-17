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

    public function testListWithIncorrectLimit()
    {
        $client = static::createClient();

        $client->request('GET', '/orders?limit=0&page=1');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testListWithIncorrectPage()
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

        $this->assertResponse($client->getResponse(), 'create');
    }

    public function testCreateWithIncorrectDestination()
    {
        $client = static::createClient();

        $client->request('POST', '/orders', [
            'origin' => ['22.334600','114.147636'],
            'destination' => ['22.421550','194.171112']
        ]);

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testCreateWithIncorrectOrigin()
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

    public function testTakeWithIncorrectState()
    {
        $client = static::createClient();

        $client->request('PATCH', '/orders/1', ['status' => 'ASSIGNED']);

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testTakeWithIncorrectOrderId()
    {
        $client = static::createClient();

        $client->request('PATCH', '/orders/2', ['status' => 'TAKEN']);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testTake()
    {
        $client = static::createClient();

        $client->request('PATCH', '/orders/1', ['status' => 'TAKEN']);

        $this->assertResponse($client->getResponse(), 'take');
    }

    public function testTakeWithTakenOrder()
    {
        $client = static::createClient();

        $client->request('PATCH', '/orders/1', ['status' => 'TAKEN']);

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }
}