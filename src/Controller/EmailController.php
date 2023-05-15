<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class EmailController extends AbstractController
{
    #[Route('/email', name: 'app_email')]
    public function sendEmail(Request $request, MailerInterface $mailer): Response
    {
        $data = json_decode($request->getContent(), true);
        $email = (new Email())
            ->from('pasante.santiago@unicatolicadelsur.edu.co')
            ->to($data['to'])
            ->subject($data['subject'])
            ->html($data['message']);

        $mailer->send($email);


        return new Response('Correo electrónico enviado');
        // return $this->render('email/index.html.twig', [
        //     'controller_name' => 'EmailController',
        // ]);
    }
}
