<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class Helpers
{
    public function json($data)
    {
        

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $encoder = [ new JsonEncoder() ];
        $normalizer = [new GetSetMethodNormalizer()];
        # $normalizer = [ new ObjectNormalizer($classMetadataFactory) ];
        $serializer = new Serializer( $normalizer, $encoder );
        $json = $serializer->serialize($data, 'json',['groups' => ['producto']] );
        $response = new Response();
        $response->setContent($json);
        $response->headers->set('Content-Type','application/json');
        $response->headers->add(array('charset'=>'UTF-8'));
        return $response;
    }
};