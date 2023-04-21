<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

class MercureController extends AbstractController
{
    #[Route('/mercure/ws', name:'app_mercure_ws')]
    public function publish(HubInterface $hub): JsonResponse
    {
        $update = new Update(
            'https://localhost:49153/books/1',
            json_encode(['approved' => 'OutOfStock'])
        );

        $hub->publish($update);

        return new JsonResponse('published!');
    }
}
