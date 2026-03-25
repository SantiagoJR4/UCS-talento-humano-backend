<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class IctusController extends AbstractController
{
    #[Route('/ictus', name: 'app_ictus')]
    public function index(): Response
    {
        return $this->render('ictus/index.html.twig', [
            'controller_name' => 'IctusController',
        ]);
    }

    #[Route('/ictus/send-a-verification-email', name: 'app_ictus_send_a_verification_email')]
    public function sendAVerificationEmail(MailerInterface $mailer, Request $request): JsonResponse
    {
        $data = $request->request->all();
        
        try {
            $email = (new TemplatedEmail())
                ->from('webmaster@unicatolicadelsur.edu.co')
                ->to($data['email'])
                ->cc('mercadeo@unicatolicadelsur.edu.co')
                ->subject('Confirmación de Registro')
                ->htmlTemplate('ictus/verification_email.twig')
                ->context([
                    'token' => $data['token'],
                    'numero' => $data['numero'],
                    // 'programa' => $data['programa'] // si lo logro obtener HOY
                ])
            ;
            $email->getHeaders()->addTextHeader('X-transport', 'alternative2');
            $mailer->send($email);
            $message = 'Correo de verificación enviado exitosamente.';
        } catch (\Throwable $th) {
            $message = 'Error al enviar el correo de verificación.';
            return new JsonResponse(['error' => $message, 'details' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }   

        return new JsonResponse(['message' => $message], Response::HTTP_OK);
    }
}
