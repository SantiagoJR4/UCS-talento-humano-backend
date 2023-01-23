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
        if ($file instanceof UploadedFile) {
            $newFileName = 'new_pdf'.time().'.'.$file->guessExtension();
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
