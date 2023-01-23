<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UploadFileController extends AbstractController
{
    #[Route('/upload/file', name: 'app_upload_file')]
    public function uploadFile(): Response
    {
        $request = Request::createFromGlobals();
        $file = $request->files->get('file');
        $name = $request->request->get('name');
        $extension = $request->request->get('extension');
        if ( $extension !== $file->guessExtension() ) {
            $response = new Response();
            $response->setStatusCode(500);
            $response->setContent('El archivo no es un '.$extension.' es un '.$file->guessExtension());
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        if ($file instanceof UploadedFile) {
            $newFileName = $name.time().'.'.$file->guessExtension();
            $file->move(
                $this->getParameter('uploads_directory'),
                $newFileName
            );
        }
        $response=new Response();
        $response->setContent(json_encode(['respuesta' => $file]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
