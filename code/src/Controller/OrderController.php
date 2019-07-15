<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Lock;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use GuzzleHttp\Client;
use FOS\RestBundle\Controller\Annotations\View;

class OrderController extends AbstractFOSRestController
{
    /**
     * @Route("/orders", name="order_create", methods={"POST"})
     * @View()
     */
    public function create(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $origin = $request->get('origin');
        $destination = $request->get('destination');

        // origin is not a pair of string
        if (count($origin) !== 2 || !is_string($origin[0]) || !is_string($origin[1])) {
            throw new BadRequestHttpException('Incorrect origin.');
        }

        // destination is not a pair of string
        if (count($destination) !== 2 || !is_string($destination[0]) || !is_string($destination[1])) {
            throw new BadRequestHttpException('Incorrect destination.');
        }

        $order = $this->createOrder($origin, $destination, $entityManager, $validator);

        return [
            'id' => $order->getId(),
            'distance' => $order->getDistance(),
            'status' => $order->getStatus()
        ];
    }

    /**
     * @Route("/orders/{id}", name="order_take", methods={"PATCH"})
     * @View()
     */
    public function take(Request $request, $id, OrderRepository $orderRepository, EntityManagerInterface $entityManager)
    {
        $status = $request->get('status');

        if (empty($status) || $status !== Order::STATUS_TAKEN) {
            throw new BadRequestHttpException('Incorrect status.');
        }

        $lock = $this->createOrderLock($id);

        if (!$lock->acquire()) {
            throw new BadRequestHttpException('Order is locked.');
        }

        $order = $orderRepository->find($id);

        if ($order->getStatus() !== Order::STATUS_UNASSIGNED) {
            throw new BadRequestHttpException('Order is already taken.');
        }

        $order->setStatus(Order::STATUS_TAKEN);
        $entityManager->flush();
        $lock->release();

        return [
            'status' => 'SUCCESS'
        ];
    }

    /**
     * @Route("/orders", name="order_list", methods={"GET"})
     * @View()
     */
    public function list(Request $request, OrderRepository $orderRepository)
    {
        $limit = $request->query->get('limit');
        $page = $request->query->get('page');

        if (preg_match('/^\d+$/', $limit) === 0 || (int) $limit < 1) {
            throw new BadRequestHttpException('The value of limit is not valid.');
        }

        if (preg_match('/^\d+$/', $page) === 0 || (int) $page < 1) {
            throw new BadRequestHttpException('The value of page is not valid.');
        }

        $queryBuilder = $orderRepository->createQueryBuilder('o');
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage((int) $limit);

        try {
            $pagerfanta->setCurrentPage((int) $page);
        } catch (OutOfRangeCurrentPageException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return $pagerfanta->getCurrentPageResults();
    }

    private function createOrder($origin, $destination, EntityManagerInterface $entityManager, ValidatorInterface $validator) : Order
    {
        $order = new Order($origin[0], $origin[1], $destination[0], $destination[1]);
        // Validate origin and destination
        $violations = $validator->validate($order);

        if ($violations->count() > 0) {
            $violation = $violations[0];

            throw new BadRequestHttpException(sprintf('%s: %s', $violation->getInvalidValue(), $violation->getMessage()));
        }

        $order->setDistance($this->getDistance($origin, $destination));
        $entityManager->persist($order);
        $entityManager->flush();

        return $order;
    }

    // Calculate distance by using google maps API
    private function getDistance($origin, $destination) : int
    {
        $client = new Client();
        $response = $client->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
            'query' => [
                'origins' => implode($origin, ','),
                'destinations' => implode($destination, ','),
                'key' => $this->getParameter('google_maps_api_key')
            ]
        ]);

        $response = json_decode($response->getBody(), true);

        // google cannot calculate the distance.
        if (!isset($response['rows'][0]['elements'][0]['status']) || $response['rows'][0]['elements'][0]['status'] !== 'OK') {
            throw new BadRequestHttpException('Cannot calculate distance');
        }

        return $response['rows'][0]['elements'][0]['distance']['value'];
    }

    private function createOrderLock($orderId) : Lock
    {
        $store = new SemaphoreStore();
        $factory = new Factory($store);

        return $factory->createLock('order:' . $orderId);
    }
}