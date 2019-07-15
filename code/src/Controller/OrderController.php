<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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

        if (empty($origin[0]) || empty($origin[1]) || count($origin) > 2 || !is_string($origin[0]) || !is_string($origin[1])) {
            throw new BadRequestHttpException('Incorrect origin.');
        }

        if (empty($destination[0]) || empty($destination[1]) || count($destination) > 2 || !is_string($destination[0]) ||
            !is_string($destination[1])
        ) {
            throw new BadRequestHttpException('Incorrect destination.');
        }

        $order = $this->createOrder($origin, $destination, $entityManager, $validator);

        return [
            'id' => $order->getId(),
            'distance' => $order->getDistance(),
            'status' => $order->getStatus()
        ];
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

        if (!isset($response['rows'][0]['elements'][0]['status']) || $response['rows'][0]['elements'][0]['status'] !== 'OK') {
            throw new BadRequestHttpException('Cannot calculate distance');
        }

        return $response['rows'][0]['elements'][0]['distance']['value'];
    }
}