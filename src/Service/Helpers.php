<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class Helpers
{
    public function serializador($data){

        $normalizer = array(new DateTimeNormalizer(), new GetSetMethodNormalizer());
        $encoders=[new JsonEncoder()];

        $serializer = new Serializer($normalizer,$encoders);
        $serializedData = $serializer->serialize($data, 'json');

        $response = new Response();
        $response->setContent($serializedData);
        $response->headers->set('Content-Type','application/json');
        $response->headers->add(array('charset' => 'UTF-8'));

        return $response;
    }
};