<?php

namespace App\Tests\Controller;

use ApiTestCase\JsonApiTestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderControllerTest extends JsonApiTestCase
{
    public function testEmptyList()
    {
        $client = $this->getClient();

        $client->request('GET', '/orders?limit=10&page=1');

        $this->assertEquals('[]', $client->getResponse()->getContent());
    }

    public function testListWithIncorrectLimit()
    {
        $this->expectException(BadRequestHttpException::class);

        $client = $this->getClient();
        $client->request('GET', '/orders?limit=0&page=1');
    }

    public function testListWithIncorrectPage()
    {
        $this->expectException(BadRequestHttpException::class);

        $client = $this->getClient();
        $client->request('GET', '/orders?limit=10&page=0');
    }

    public function testCreate()
    {
        $client = $this->getClient();

        $client->request('POST', '/orders', [
            'origin' => ['22.334600','114.147636'],
            'destination' => ['22.421550','114.171112']
        ]);

        $this->assertResponse($client->getResponse(), 'create');
    }

    public function testCreateWithIncorrectDestination()
    {
        $this->expectException(BadRequestHttpException::class);

        $client = $this->getClient();
        $client->request('POST', '/orders', [
            'origin' => ['22.334600','114.147636'],
            'destination' => ['22.421550','194.171112']
        ]);
    }

    public function testCreateWithIncorrectOrigin()
    {
        $this->expectException(BadRequestHttpException::class);

        $client = $this->getClient();
        $client->request('POST', '/orders', [
            'origin' => ['92.334600','114.147636'],
            'destination' => ['22.421550','194.171112']
        ]);
    }

    public function testList()
    {
        $client = $this->getClient();

        $client->request('GET', '/orders?limit=10&page=1');

        $this->assertResponse($client->getResponse(), 'list');
    }

    public function testTakeWithIncorrectState()
    {
        $this->expectException(BadRequestHttpException::class);

        $client = $this->getClient();
        $client->request('PATCH', '/orders/1', ['status' => 'ASSIGNED']);
    }

    public function testTakeWithIncorrectOrderId()
    {
        $this->expectException(NotFoundHttpException::class);

        $client = $this->getClient();
        $client->request('PATCH', '/orders/2', ['status' => 'TAKEN']);
    }

    public function testTake()
    {
        $client = $this->getClient();

        $client->request('PATCH', '/orders/1', ['status' => 'TAKEN']);

        $this->assertResponse($client->getResponse(), 'take');
    }

    public function testTakeWithTakenOrder()
    {
        $this->expectException(BadRequestHttpException::class);

        $client = $this->getClient();
        $client->request('PATCH', '/orders/1', ['status' => 'TAKEN']);
    }

    private function getClient()
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        
        return $client;
    }
}
