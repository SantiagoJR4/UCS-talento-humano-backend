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

    #[Route('/ictus/send-risk-email', name: 'app_ictus_send_risk_email')]
    public function sendRiskEmail(MailerInterface $mailer, Request $request): JsonResponse
    {
        $data = $request->request->all();
        
        try {
            $email = (new TemplatedEmail())
                ->from('webmaster@unicatolicadelsur.edu.co')
                ->to($data['correoDocente'])
                ->cc($data['directorPrograma'])
                ->bcc('permanencia@unicatolicadelsur.edu.co')
                ->subject($data['mensaje'])
                ->htmlTemplate('ictus/risk.html.twig')
                ->context([
                    'mensaje' => $data['mensaje'],
                    'programa' => $data['programa'],
                    'datos' => $data['datos']
                ])
            ;
            $email->getHeaders()->addTextHeader('X-transport', 'alternative2');
            $mailer->send($email);
            $message = 'Correo de alerta de riesgo enviado exitosamente.';
        } catch (\Throwable $th) {
            $message = 'Error al enviar el correo de alerta de riesgo.';
            return new JsonResponse(['error' => $message, 'details' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }   

        return new JsonResponse(['message' => $message], Response::HTTP_OK);
    }

    #[Route('/ictus/send-admitted-student-email', name: 'app_ictus_send_admitted_student_email')]
    public function sendAdmittedStudentEmail(MailerInterface $mailer, Request $request): JsonResponse
    {
        $data = $request->request->all();
        
        try {
            $email = (new TemplatedEmail())
                ->from('webmaster@unicatolicadelsur.edu.co')
                ->to($data['correoEstudiante'])
                ->cc($data['correoMercadeo'])
                // ->bcc('permanencia@unicatolicadelsur.edu.co')
                ->subject($data['mensaje'])
                ->htmlTemplate('ictus/admitted_student.html.twig')
                ->context([
                    'nombres' => $data['nombres'],
                    'apellidos' => $data['apellidos'],
                    'numero' => $data['numero'],
                    'tipoIdentificacion' => $data['tipoIdentificacion'],
                    'programa' => $data['programa'],
                    'programaId' => $data['programaId'],
                    'inscrito' => $data['inscrito'],
                    'periodo' => $data['periodo']
                ])
            ;
            $email->getHeaders()->addTextHeader('X-transport', 'alternative2');
            $mailer->send($email);
            $message = 'Correo de estudiante admitido enviado exitosamente.';
        } catch (\Throwable $th) {
            $message = 'Error al enviar el correo de estudiante admitido.';
            return new JsonResponse(['error' => $message, 'details' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }   

        return new JsonResponse(['message' => $message], Response::HTTP_OK);
    }

    // cquyvrrhiedhshnt

    
}
